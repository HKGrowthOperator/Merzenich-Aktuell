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
function wp_mail($to,$subject,$body){ $GLOBALS['mail'][]=compact('to','subject','body'); return true; }
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

echo "\n".($fehler===0?"Alle Pruefungen bestanden.\n":"$fehler Pruefung(en) fehlgeschlagen.\n");
exit($fehler===0?0:1);
