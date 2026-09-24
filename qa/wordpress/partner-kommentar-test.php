<?php
/**
 * Partner-Rollen und Kommentarfreigabe ohne WordPress-Laufzeit testen.
 * Aufruf: php qa/wordpress/partner-kommentar-test.php
 */
define('ABSPATH', __DIR__);

class WP_User {
    public int $ID;
    public array $roles;
    function __construct(int $id=1, array $roles=[]) { $this->ID=$id; $this->roles=$roles; }
    function exists(): bool { return $this->ID > 0; }
}
class WP_Comment {
    public $comment_author_email=''; public $comment_author=''; public $comment_post_ID=0; public $comment_ID=0; public $comment_approved='0';
}
class WP_Post { public $ID=0; public $post_type='post'; public $post_author=0; public $post_status='draft'; }
class WP_Query { public $found_posts=0; function __construct($a=[]) {} }
class WP_REST_Request implements ArrayAccess {
    private array $d=[]; function offsetExists($o):bool{return isset($this->d[$o]);}
    function offsetGet($o):mixed{return $this->d[$o]??null;} function offsetSet($o,$v):void{$this->d[$o]=$v;}
    function offsetUnset($o):void{unset($this->d[$o]);}
}
class WP_Error { function __construct(...$a){} }

$GLOBALS['user']=new WP_User(7,['ma_sport_partner']);
$GLOBALS['can_moderate']=false;
$GLOBALS['mail']=[];

function wp_get_current_user(){ return $GLOBALS['user']; }
function get_userdata($id){ return $id===$GLOBALS['user']->ID ? $GLOBALS['user'] : new WP_User(0,[]); }
function get_current_user_id(){ return $GLOBALS['user']->ID; }
function current_user_can($cap,...$args){ return $cap==='moderate_comments' ? $GLOBALS['can_moderate'] : true; }
function sanitize_email($v){ return trim((string)$v); }
function is_email($v){ return filter_var($v,FILTER_VALIDATE_EMAIL)!==false; }
function get_the_title($id){ return 'Testbeitrag'; }
function get_comment_link($c){ return 'https://example.invalid/test/#comment-'.$c->comment_ID; }
function wp_mail($to,$subject,$body){ $GLOBALS['mail'][]=compact('to','subject','body'); return $GLOBALS['mail_ok'] ?? true; }
// Kleiner Kommentarspeicher fuer die Sammelfreigabe.
$GLOBALS['kommentare']=[];
function get_comment($id){ return $GLOBALS['kommentare'][$id] ?? null; }
function get_comments($a){
    $held=array_values(array_filter($GLOBALS['kommentare'],fn($c)=>(string)$c->comment_approved==='0'));
    if(!empty($a['count'])) return count($held);
    $held=array_slice($held,(int)($a['offset']??0),(int)($a['number']??100));
    return ($a['fields']??'')==='ids' ? array_map(fn($c)=>$c->comment_ID,$held) : $held;
}
function wp_set_comment_status($id,$status,$err=false){
    $c=$GLOBALS['kommentare'][$id]??null; if(!$c) return false;
    $alt=(string)$c->comment_approved==='0'?'unapproved':'approved';
    $c->comment_approved='1'; ma_comment_approval_mail('approved',$alt,$c); return true;
}
function check_admin_referer($a){ return true; }
function sanitize_key($s){ return strtolower(preg_replace('/[^a-z0-9_\-]/i','',(string)$s)); }
function wp_unslash($s){ return $s; }

require __DIR__.'/../../wordpress/plugin/merzenich-aktuell-core/includes/partners.php';
require __DIR__.'/../../wordpress/plugin/merzenich-aktuell-core/includes/comments.php';

$fehler=0;
function pruefe($name,$ist,$soll){ global $fehler; $ok=$ist===$soll; if(!$ok)$fehler++; printf("  %-64s %s\n",$name,$ok?'ok':'FEHLER (ist: '.var_export($ist,true).')'); }

echo "Partner-Policies\n";
$p=ma_current_partner_policy();
pruefe('Sport-Partner erkannt',$p['role']??'','ma_sport_partner');
pruefe('Sport-Partner darf nur post', $p['post_types'], ['post']);
pruefe('Sport-Kategorie festgelegt', $p['categories'], ['sport']);

$GLOBALS['user']=new WP_User(8,['ma_wirtschaft_partner']);
$p=ma_current_partner_policy();
pruefe('Unternehmens-Partner erkannt',$p['role']??'','ma_wirtschaft_partner');
pruefe('Unternehmens-Partner darf Tipp einreichen',in_array('ma_tip',$p['post_types']??[],true),true);
pruefe('Unternehmens-Partner darf Werbemittel einreichen',in_array('ma_ad',$p['post_types']??[],true),true);

$GLOBALS['user']=new WP_User(7,['ma_sport_partner']);
echo "\nStatus-Gate\n";
$d=ma_partner_force_pending(['post_type'=>'post','post_status'=>'publish'],[]);
pruefe('Partner-Publish wird pending',$d['post_status'],'pending');
$d=ma_partner_force_pending(['post_type'=>'post','post_status'=>'draft'],[]);
pruefe('Entwurf bleibt Entwurf',$d['post_status'],'draft');
$d=ma_partner_force_pending(['post_type'=>'ma_property','post_status'=>'publish'],[]);
pruefe('Nicht erlaubter Typ bleibt unveroeffentlicht',$d['post_status'],'draft');

echo "\nKommentar-Moderation\n";
$GLOBALS['can_moderate']=false;
pruefe('Frontend-Kommentar wird gehalten',ma_comments_force_moderation(1,[]),0);
pruefe('Spam bleibt Spam',ma_comments_force_moderation('spam',[]),'spam');
$GLOBALS['can_moderate']=true;
pruefe('Redaktion darf genehmigt erzeugen',ma_comments_force_moderation(1,[]),1);

echo "\nGenehmigungs-Mail\n";
$c=new WP_Comment(); $c->comment_ID=12; $c->comment_post_ID=99; $c->comment_author='Max'; $c->comment_author_email='max@example.de';
ma_comment_approval_mail('approved','hold',$c);
pruefe('Mail nach Freigabe gesendet',count($GLOBALS['mail']),1);
pruefe('Mail geht an Verfasser',$GLOBALS['mail'][0]['to']??'','max@example.de');
ma_comment_approval_mail('approved','approved',$c);
pruefe('Keine zweite Mail ohne Statuswechsel',count($GLOBALS['mail']),1);

echo "\nRollen Polizei und Feuerwehr\n";
$pol=ma_partner_policies();
pruefe('Polizei-Rolle vorhanden',isset($pol['ma_polizei_partner']),true);
pruefe('Feuerwehr-Rolle vorhanden',isset($pol['ma_feuerwehr_partner']),true);
pruefe('Polizei landet in Blaulicht',$pol['ma_polizei_partner']['categories'],['blaulicht']);
pruefe('Feuerwehr landet in Blaulicht',$pol['ma_feuerwehr_partner']['categories'],['blaulicht']);
pruefe('Alte Blaulicht-Rolle bleibt, aber veraltet',!empty($pol['ma_blaulicht_partner']['veraltet']),true);
$GLOBALS['user']=new WP_User(9,['ma_feuerwehr_partner']);
$d=ma_partner_force_pending(['post_type'=>'post','post_status'=>'publish'],[]);
pruefe('Feuerwehr-Beitrag wird pending',$d['post_status'],'pending');
$GLOBALS['user']=new WP_User(10,['ma_blaulicht_partner']);
pruefe('Bestehender Blaulicht-Zugang funktioniert weiter',ma_current_partner_policy()['role']??'','ma_blaulicht_partner');

echo "\nAlle wartenden genehmigen (ueber mehrere Seiten)\n";
$GLOBALS['can_moderate']=true; $GLOBALS['mail']=[];
$_SERVER['REQUEST_METHOD']='POST'; $_POST=['approve_all'=>'1'];
for($i=1;$i<=230;$i++){ $k=new WP_Comment(); $k->comment_ID=$i; $k->comment_post_ID=99; $k->comment_author_email="l$i@example.de"; $GLOBALS['kommentare'][$i]=$k; }
$m=ma_comment_moderation_process();
pruefe('Alle 230 genehmigt',count(array_filter($GLOBALS['kommentare'],fn($c)=>$c->comment_approved==='1')),230);
pruefe('Meldung nennt 230',str_starts_with($m,'230 '),true);
pruefe('230 Freigabe-Mails',count($GLOBALS['mail']),230);

echo "\nAuswahl und Mail-Hinweis\n";
$GLOBALS['mail_ok']=false; $GLOBALS['ma_comment_mail_fehler']=0;
foreach([301,302,303] as $i){ $k=new WP_Comment(); $k->comment_ID=$i; $k->comment_post_ID=99; $k->comment_author_email="x$i@example.de"; $GLOBALS['kommentare'][$i]=$k; }
$_POST=['approve_selected'=>'1','comment_ids'=>['301','303']];
$m=ma_comment_moderation_process();
pruefe('Nur ausgewaehlte genehmigt',[$GLOBALS['kommentare'][301]->comment_approved,$GLOBALS['kommentare'][302]->comment_approved,$GLOBALS['kommentare'][303]->comment_approved],['1','0','1']);
pruefe('Hinweis auf gescheiterte Mails',str_contains($m,'2 Benachrichtigungen per E-Mail konnten nicht versendet werden'),true);

echo "\n".($fehler===0?"Alle Pruefungen bestanden.\n":"$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler===0?0:1);
