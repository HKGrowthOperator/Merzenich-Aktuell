/* Minimaler YAML-Parser für Frontmatter, wie Decap CMS und Redakteure es schreiben.
   Unterstützt: Skalare, Anführungszeichen, Listen (- item), Objektlisten (- key: v),
   verschachtelte Objekte, Block-Skalare (| und >), Kommentare, Inline-Listen [a, b]. */

function scalar(raw) {
  const s = raw.trim();
  if (s === '' || s === '~' || s === 'null') return null;
  if (s === 'true') return true;
  if (s === 'false') return false;
  if ((s.startsWith('"') && s.endsWith('"'))) return JSON.parse(s.replace(/\n/g, '\\n'));
  if ((s.startsWith("'") && s.endsWith("'"))) return s.slice(1, -1).replace(/''/g, "'");
  if (s.startsWith('{') && s.endsWith('}')) {
    const inner = s.slice(1, -1).trim(); if (!inner) return {};
    const parts = []; let cur = '', q = null;
    for (const c of inner) { if (q) { cur += c; if (c === q) q = null; continue; } if (c === '"' || c === "'") { q = c; cur += c; continue; } if (c === ',') { parts.push(cur); cur = ''; continue; } cur += c; }
    parts.push(cur);
    const o = {};
    for (const p of parts) { const m = p.trim().match(/^([\w\-äöüÄÖÜß.]+)\s*:\s*([\s\S]*)$/); if (m) o[m[1]] = scalar(m[2]); }
    return o;
  }
  if (s.startsWith('[') && s.endsWith(']')) {
    const inner = s.slice(1, -1).trim();
    return inner ? inner.split(',').map(x => scalar(x)) : [];
  }
  if (/^-?\d+(\.\d+)?$/.test(s)) return Number(s);
  return s;
}

function indentOf(line) { return line.match(/^ */)[0].length; }

function stripComment(line) {
  // Kommentar nur außerhalb von Anführungszeichen entfernen
  let q = null, out = '';
  for (let i = 0; i < line.length; i++) {
    const c = line[i];
    if (q) { out += c; if (c === q) q = null; continue; }
    if (c === '"' || c === "'") { q = c; out += c; continue; }
    if (c === '#' && (i === 0 || /\s/.test(line[i - 1]))) break;
    out += c;
  }
  return out.replace(/\s+$/, '');
}

export function parseYaml(text) {
  const lines = text.replace(/\r\n?/g, '\n').split('\n');
  let i = 0;

  function readBlock(baseIndent, style) {
    const buf = [];
    while (i < lines.length) {
      const l = lines[i];
      if (l.trim() === '') { buf.push(''); i++; continue; }
      if (indentOf(l) <= baseIndent) break;
      buf.push(l.slice(Math.min(indentOf(l), baseIndent + 2)));
      i++;
    }
    while (buf.length && buf[buf.length - 1] === '') buf.pop();
    if (style === '|') return buf.join('\n');
    // gefaltet: einfache Zeilenumbrüche werden zu Leerzeichen, Leerzeilen bleiben Absätze
    return buf.join('\n').replace(/([^\n])\n(?!\n)/g, '$1 ').replace(/\n\n/g, '\n');
  }

  function parseValue(rest, indent) {
    const r = rest.trim();
    if (r === '|' || r === '>' || r === '|-' || r === '>-') return readBlock(indent, r[0]);
    if (r === '') {
      // Kind-Struktur folgt (Objekt oder Liste) oder leer
      const next = peekNext();
      if (next === null) return null;
      if (indentOf(next) > indent) return parseNode(indentOf(next));
      if (indentOf(next) === indent && next.trim().startsWith('- ')) return parseNode(indent);
      return null;
    }
    return scalar(r);
  }

  function peekNext() {
    let j = i;
    while (j < lines.length && stripComment(lines[j]).trim() === '') j++;
    return j < lines.length ? lines[j] : null;
  }

  function parseNode(indent) {
    const first = peekNext();
    if (first === null) return null;
    if (first.trim().startsWith('- ')) return parseList(indent);
    return parseMap(indent);
  }

  function parseList(indent) {
    const arr = [];
    while (i < lines.length) {
      const raw = stripComment(lines[i]);
      if (raw.trim() === '') { i++; continue; }
      const ind = indentOf(raw);
      if (ind < indent || !raw.trim().startsWith('- ')) break;
      if (ind > indent) break;
      const rest = raw.trim().slice(2);
      i++;
      if (/^[\w\-äöüÄÖÜß]+\s*:/.test(rest) && !/^https?:/.test(rest)) {
        // Objekt in der Liste: erste Zeile gehört zum Objekt
        lines[i - 1] = ' '.repeat(ind + 2) + rest;
        i--;
        arr.push(parseMap(ind + 2));
      } else {
        arr.push(parseValue(rest, ind));
      }
    }
    return arr;
  }

  function parseMap(indent) {
    const obj = {};
    while (i < lines.length) {
      const raw = stripComment(lines[i]);
      if (raw.trim() === '') { i++; continue; }
      const ind = indentOf(raw);
      if (ind < indent) break;
      if (ind > indent) { i++; continue; } // verwaiste Zeile
      if (raw.trim().startsWith('- ')) break;
      const m = raw.trim().match(/^([\w\-äöüÄÖÜß.]+)\s*:\s?(.*)$/);
      if (!m) { i++; continue; }
      i++;
      obj[m[1]] = parseValue(m[2], ind);
    }
    return obj;
  }

  return parseMap(indentOf(peekNext() || '')) || {};
}

export function parseFrontmatter(src) {
  const m = src.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n?([\s\S]*)$/);
  if (!m) return { data: {}, body: src };
  return { data: parseYaml(m[1]), body: m[2] };
}
