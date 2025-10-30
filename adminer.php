<?php
/** Adminer - Compact database management
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2007 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 5.4.1
*/namespace
Adminer;const
VERSION="5.4.1";error_reporting(24575);set_error_handler(function($Cc,$Ec){return!!preg_match('~^Undefined (array key|offset|index)~',$Ec);},E_WARNING|E_NOTICE);$ad=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($ad||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$X){$tj=filter_input_array(constant("INPUT$X"),FILTER_UNSAFE_RAW);if($tj)$$X=$tj;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection($g=null){return($g?:Db::$instance);}function
adminer(){return
Adminer::$instance;}function
driver(){return
Driver::$instance;}function
connect(){$Fb=adminer()->credentials();$J=Driver::connect($Fb[0],$Fb[1],$Fb[2]);return(is_object($J)?$J:null);}function
idf_unescape($u){if(!preg_match('~^[`\'"[]~',$u))return$u;$Ie=substr($u,-1);return
str_replace($Ie.$Ie,$Ie,substr($u,1,-1));}function
q($Q){return
connection()->quote($Q);}function
escape_string($X){return
substr(q($X),1,-1);}function
idx($va,$x,$k=null){return($va&&array_key_exists($x,$va)?$va[$x]:$k);}function
number($X){return
preg_replace('~[^0-9]+~','',$X);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes(array$ah,$ad=false){if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){while(list($x,$X)=each($ah)){foreach($X
as$Ae=>$W){unset($ah[$x][$Ae]);if(is_array($W)){$ah[$x][stripslashes($Ae)]=$W;$ah[]=&$ah[$x][stripslashes($Ae)];}else$ah[$x][stripslashes($Ae)]=($ad?$W:stripslashes($W));}}}}function
bracket_escape($u,$Ca=false){static$cj=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($u,($Ca?array_flip($cj):$cj));}function
min_version($Lj,$We="",$g=null){$g=connection($g);$Vh=$g->server_info;if($We&&preg_match('~([\d.]+)-MariaDB~',$Vh,$A)){$Vh=$A[1];$Lj=$We;}return$Lj&&version_compare($Vh,$Lj)>=0;}function
charset(Db$f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
ini_bool($ke){$X=ini_get($ke);return(preg_match('~^(on|true|yes)$~i',$X)||(int)$X);}function
ini_bytes($ke){$X=ini_get($ke);switch(strtolower(substr($X,-1))){case'g':$X=(int)$X*1024;case'm':$X=(int)$X*1024;case'k':$X=(int)$X*1024;}return$X;}function
sid(){static$J;if($J===null)$J=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$J;}function
set_password($Kj,$N,$V,$F){$_SESSION["pwds"][$Kj][$N][$V]=($_COOKIE["adminer_key"]&&is_string($F)?array(encrypt_string($F,$_COOKIE["adminer_key"])):$F);}function
get_password(){$J=get_session("pwds");if(is_array($J))$J=($_COOKIE["adminer_key"]?decrypt_string($J[0],$_COOKIE["adminer_key"]):false);return$J;}function
get_val($H,$m=0,$tb=null){$tb=connection($tb);$I=$tb->query($H);if(!is_object($I))return
false;$K=$I->fetch_row();return($K?$K[$m]:false);}function
get_vals($H,$d=0){$J=array();$I=connection()->query($H);if(is_object($I)){while($K=$I->fetch_row())$J[]=$K[$d];}return$J;}function
get_key_vals($H,$g=null,$Yh=true){$g=connection($g);$J=array();$I=$g->query($H);if(is_object($I)){while($K=$I->fetch_row()){if($Yh)$J[$K[0]]=$K[1];else$J[]=$K[0];}}return$J;}function
get_rows($H,$g=null,$l="<p class='error'>"){$tb=connection($g);$J=array();$I=$tb->query($H);if(is_object($I)){while($K=$I->fetch_assoc())$J[]=$K;}elseif(!$I&&!$g&&$l&&(defined('Adminer\PAGE_HEADER')||$l=="-- "))echo$l.error()."\n";return$J;}function
unique_array($K,array$w){foreach($w
as$v){if(preg_match("~PRIMARY|UNIQUE~",$v["type"])){$J=array();foreach($v["columns"]as$x){if(!isset($K[$x]))continue
2;$J[$x]=$K[$x];}return$J;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where(array$Z,array$n=array()){$J=array();foreach((array)$Z["where"]as$x=>$X){$x=bracket_escape($x,true);$d=escape_key($x);$m=idx($n,$x,array());$Xc=$m["type"];$J[]=$d.(JUSH=="sql"&&$Xc=="json"?" = CAST(".q($X)." AS JSON)":(JUSH=="pgsql"&&preg_match('~^json~',$Xc)?"::jsonb = ".q($X)."::jsonb":(JUSH=="sql"&&is_numeric($X)&&preg_match('~\.~',$X)?" LIKE ".q($X):(JUSH=="mssql"&&strpos($Xc,"datetime")===false?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$X)):" = ".unconvert_field($m,q($X))))));if(JUSH=="sql"&&preg_match('~char|text~',$Xc)&&preg_match("~[^ -@]~",$X))$J[]="$d = ".q($X)." COLLATE ".charset(connection())."_bin";}foreach((array)$Z["null"]as$x)$J[]=escape_key($x)." IS NULL";return
implode(" AND ",$J);}function
where_check($X,array$n=array()){parse_str($X,$Wa);remove_slashes(array(&$Wa));return
where($Wa,$n);}function
where_link($s,$d,$Y,$Xf="="){return"&where%5B$s%5D%5Bcol%5D=".urlencode($d)."&where%5B$s%5D%5Bop%5D=".urlencode(($Y!==null?$Xf:"IS NULL"))."&where%5B$s%5D%5Bval%5D=".urlencode($Y);}function
convert_fields(array$e,array$n,array$M=array()){$J="";foreach($e
as$x=>$X){if($M&&!in_array(idf_escape($x),$M))continue;$wa=convert_field($n[$x]);if($wa)$J
.=", $wa AS ".idf_escape($x);}return$J;}function
cookie($B,$Y,$Pe=2592000){header("Set-Cookie: $B=".urlencode($Y).($Pe?"; expires=".gmdate("D, d M Y H:i:s",time()+$Pe)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).(HTTPS?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
get_settings($Bb){parse_str($_COOKIE[$Bb],$Zh);return$Zh;}function
get_setting($x,$Bb="adminer_settings",$k=null){return
idx(get_settings($Bb),$x,$k);}function
save_settings(array$Zh,$Bb="adminer_settings"){$Y=http_build_query($Zh+get_settings($Bb));cookie($Bb,$Y);$_COOKIE[$Bb]=$Y;}function
restart_session(){if(!ini_bool("session.use_cookies")&&(!function_exists('session_status')||session_status()==1))session_start();}function
stop_session($id=false){$Cj=ini_bool("session.use_cookies");if(!$Cj||$id){session_write_close();if($Cj&&@ini_set("session.use_cookies",'0')===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$X){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$X;}function
auth_url($Kj,$N,$V,$j=null){$zj=remove_from_uri(implode("|",array_keys(SqlDriver::$drivers))."|username|ext|".($j!==null?"db|":"").($Kj=='mssql'||$Kj=='pgsql'?"":"ns|").session_name());preg_match('~([^?]*)\??(.*)~',$zj,$A);return"$A[1]?".(sid()?SID."&":"").($Kj!="server"||$N!=""?urlencode($Kj)."=".urlencode($N)."&":"").($_GET["ext"]?"ext=".urlencode($_GET["ext"])."&":"")."username=".urlencode($V).($j!=""?"&db=".urlencode($j):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($Se,$lf=null){if($lf!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($Se!==null?$Se:$_SERVER["REQUEST_URI"]))][]=$lf;}if($Se!==null){if($Se=="")$Se=".";header("Location: $Se");exit;}}function
query_redirect($H,$Se,$lf,$jh=true,$Jc=true,$Sc=false,$Pi=""){if($Jc){$oi=microtime(true);$Sc=!connection()->query($H);$Pi=format_time($oi);}$ii=($H?adminer()->messageQuery($H,$Pi,$Sc):"");if($Sc){adminer()->error
.=error().$ii.script("messagesPrint();")."<br>";return
false;}if($jh)redirect($Se,$lf.$ii);return
true;}class
Queries{static$queries=array();static$start=0;}function
queries($H){if(!Queries::$start)Queries::$start=microtime(true);Queries::$queries[]=(preg_match('~;$~',$H)?"DELIMITER ;;\n$H;\nDELIMITER ":$H).";";return
connection()->query($H);}function
apply_queries($H,array$T,$Fc='Adminer\table'){foreach($T
as$R){if(!queries("$H ".$Fc($R)))return
false;}return
true;}function
queries_redirect($Se,$lf,$jh){$eh=implode("\n",Queries::$queries);$Pi=format_time(Queries::$start);return
query_redirect($eh,$Se,$lf,$jh,false,!$jh,$Pi);}function
format_time($oi){return
sprintf('%.3f s',max(0,microtime(true)-$oi));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($ug=""){return
substr(preg_replace("~(?<=[?&])($ug".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
get_file($x,$Rb=false,$Xb=""){$Zc=$_FILES[$x];if(!$Zc)return
null;foreach($Zc
as$x=>$X)$Zc[$x]=(array)$X;$J='';foreach($Zc["error"]as$x=>$l){if($l)return$l;$B=$Zc["name"][$x];$Xi=$Zc["tmp_name"][$x];$yb=file_get_contents($Rb&&preg_match('~\.gz$~',$B)?"compress.zlib://$Xi":$Xi);if($Rb){$oi=substr($yb,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$oi))$yb=iconv("utf-16","utf-8",$yb);elseif($oi=="\xEF\xBB\xBF")$yb=substr($yb,3);}$J
.=$yb;if($Xb)$J
.=(preg_match("($Xb\\s*\$)",$yb)?"":$Xb)."\n\n";}return$J;}function
upload_error($l){$gf=($l==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($l?'Unable to upload a file.'.($gf?" ".sprintf('Maximum allowed file size is %sB.',$gf):""):'File does not exist.');}function
repeat_pattern($Gg,$y){return
str_repeat("$Gg{0,65535}",$y/65535)."$Gg{0,".($y%65535)."}";}function
is_utf8($X){return(preg_match('~~u',$X)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$X));}function
format_number($X){return
strtr(number_format($X,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($X){return
preg_replace('~\W~i','-',$X);}function
table_status1($R,$Tc=false){$J=table_status($R,$Tc);return($J?reset($J):array("Name"=>$R));}function
column_foreign_keys($R){$J=array();foreach(adminer()->foreignKeys($R)as$p){foreach($p["source"]as$X)$J[$X][]=$p;}return$J;}function
fields_from_edit(){$J=array();foreach((array)$_POST["field_keys"]as$x=>$X){if($X!=""){$X=bracket_escape($X);$_POST["function"][$X]=$_POST["field_funs"][$x];$_POST["fields"][$X]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$X){$B=bracket_escape($x,true);$J[$B]=array("field"=>$B,"privileges"=>array("insert"=>1,"update"=>1,"where"=>1,"order"=>1),"null"=>1,"auto_increment"=>($x==driver()->primary),);}return$J;}function
dump_headers($Qd,$wf=false){$J=adminer()->dumpHeaders($Qd,$wf);$qg=$_POST["output"];if($qg!="text")header("Content-Disposition: attachment; filename=".adminer()->dumpFilename($Qd).".$J".($qg!="file"&&preg_match('~^[0-9a-z]+$~',$qg)?".$qg":""));session_write_close();if(!ob_get_level())ob_start(null,4096);ob_flush();flush();return$J;}function
dump_csv(array$K){foreach($K
as$x=>$X){if(preg_match('~["\n,;\t]|^0.|\.\d*0$~',$X)||$X==="")$K[$x]='"'.str_replace('"','""',$X).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$K)."\r\n";}function
apply_sql_function($r,$d){return($r?($r=="unixepoch"?"DATETIME($d, '$r')":($r=="count distinct"?"COUNT(DISTINCT ":strtoupper("$r("))."$d)"):$d);}function
get_temp_dir(){$J=ini_get("upload_tmp_dir");if(!$J){if(function_exists('sys_get_temp_dir'))$J=sys_get_temp_dir();else{$o=@tempnam("","");if(!$o)return'';$J=dirname($o);unlink($o);}}return$J;}function
file_open_lock($o){if(is_link($o))return;$q=@fopen($o,"c+");if(!$q)return;@chmod($o,0660);if(!flock($q,LOCK_EX)){fclose($q);return;}return$q;}function
file_write_unlock($q,$Lb){rewind($q);fwrite($q,$Lb);ftruncate($q,strlen($Lb));file_unlock($q);}function
file_unlock($q){flock($q,LOCK_UN);fclose($q);}function
first(array$va){return
reset($va);}function
password_file($h){$o=get_temp_dir()."/adminer.key";if(!$h&&!file_exists($o))return'';$q=file_open_lock($o);if(!$q)return'';$J=stream_get_contents($q);if(!$J){$J=rand_string();file_write_unlock($q,$J);}else
file_unlock($q);return$J;}function
rand_string(){return
md5(uniqid(strval(mt_rand()),true));}function
select_value($X,$_,array$m,$Oi){if(is_array($X)){$J="";foreach($X
as$Ae=>$W)$J
.="<tr>".($X!=array_values($X)?"<th>".h($Ae):"")."<td>".select_value($W,$_,$m,$Oi);return"<table>$J</table>";}if(!$_)$_=adminer()->selectLink($X,$m);if($_===null){if(is_mail($X))$_="mailto:$X";if(is_url($X))$_=$X;}$J=adminer()->editVal($X,$m);if($J!==null){if(!is_utf8($J))$J="\0";elseif($Oi!=""&&is_shortable($m))$J=shorten_utf8($J,max(0,+$Oi));else$J=h($J);}return
adminer()->selectVal($J,$_,$m,$X);}function
is_blob(array$m){return
preg_match('~blob|bytea|raw|file~',$m["type"])&&!in_array($m["type"],idx(driver()->structuredTypes(),'User types',array()));}function
is_mail($tc){$xa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$Gg="$xa+(\\.$xa+)*@($gc?\\.)+$gc";return
is_string($tc)&&preg_match("(^$Gg(,\\s*$Gg)*\$)i",$tc);}function
is_url($Q){$gc='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($gc?\\.)+$gc(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$Q);}function
is_shortable(array$m){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea|hstore~',$m["type"]);}function
host_port($N){return(preg_match('~^(\[(.+)]|([^:]+)):([^:]+)$~',$N,$A)?array($A[2].$A[3],$A[4]):array($N,''));}function
count_rows($R,array$Z,$ue,array$wd){$H=" FROM ".table($R).($Z?" WHERE ".implode(" AND ",$Z):"");return($ue&&(JUSH=="sql"||count($wd)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$wd).")$H":"SELECT COUNT(*)".($ue?" FROM (SELECT 1$H GROUP BY ".implode(", ",$wd).") x":$H));}function
slow_query($H){$j=adminer()->database();$Qi=adminer()->queryTimeout();$di=driver()->slowQuery($H,$Qi);$g=null;if(!$di&&support("kill")){$g=connect();if($g&&($j==""||$g->select_db($j))){$De=get_val(connection_id(),0,$g);echo
script("const timeout = setTimeout(() => { ajax('".js_escape(ME)."script=kill', function () {}, 'kill=$De&token=".get_token()."'); }, 1000 * $Qi);");}}ob_flush();flush();$J=@get_key_vals(($di?:$H),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$J;}function
get_token(){$hh=rand(1,1e6);return($hh^$_SESSION["token"]).":$hh";}function
verify_token(){list($Yi,$hh)=explode(":",$_POST["token"]);return($hh^$_SESSION["token"])==$Yi;}function
lzw_decompress($Ia){$cc=256;$Ja=8;$gb=array();$uh=0;$vh=0;for($s=0;$s<strlen($Ia);$s++){$uh=($uh<<8)+ord($Ia[$s]);$vh+=8;if($vh>=$Ja){$vh-=$Ja;$gb[]=$uh>>$vh;$uh&=(1<<$vh)-1;$cc++;if($cc>>$Ja)$Ja++;}}$bc=range("\0","\xFF");$J="";$Uj="";foreach($gb
as$s=>$fb){$sc=$bc[$fb];if(!isset($sc))$sc=$Uj.$Uj[0];$J
.=$sc;if($s)$bc[]=$Uj.$sc[0];$Uj=$sc;}return$J;}function
script($fi,$bj="\n"){return"<script".nonce().">$fi</script>$bj";}function
script_src($_j,$Ub=false){return"<script src='".h($_j)."'".nonce().($Ub?" defer":"")."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
input_hidden($B,$Y=""){return"<input type='hidden' name='".h($B)."' value='".h($Y)."'>\n";}function
input_token(){return
input_hidden("token",get_token());}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($Q){return
str_replace("\0","&#0;",htmlspecialchars($Q,ENT_QUOTES,'utf-8'));}function
nl_br($Q){return
str_replace("\n","<br>",$Q);}function
checkbox($B,$Y,$Za,$Fe="",$Wf="",$db="",$He=""){$J="<input type='checkbox' name='$B' value='".h($Y)."'".($Za?" checked":"").($He?" aria-labelledby='$He'":"").">".($Wf?script("qsl('input').onclick = function () { $Wf };",""):"");return($Fe!=""||$db?"<label".($db?" class='$db'":"").">$J".h($Fe)."</label>":$J);}function
optionlist($bg,$Nh=null,$Dj=false){$J="";foreach($bg
as$Ae=>$W){$cg=array($Ae=>$W);if(is_array($W)){$J
.='<optgroup label="'.h($Ae).'">';$cg=$W;}foreach($cg
as$x=>$X)$J
.='<option'.($Dj||is_string($x)?' value="'.h($x).'"':'').($Nh!==null&&($Dj||is_string($x)?(string)$x:$X)===$Nh?' selected':'').'>'.h($X);if(is_array($W))$J
.='</optgroup>';}return$J;}function
html_select($B,array$bg,$Y="",$Vf="",$He=""){static$Fe=0;$Ge="";if(!$He&&substr($bg[""],0,1)=="("){$Fe++;$He="label-$Fe";$Ge="<option value='' id='$He'>".h($bg[""]);unset($bg[""]);}return"<select name='".h($B)."'".($He?" aria-labelledby='$He'":"").">".$Ge.optionlist($bg,$Y)."</select>".($Vf?script("qsl('select').onchange = function () { $Vf };",""):"");}function
html_radios($B,array$bg,$Y="",$Rh=""){$J="";foreach($bg
as$x=>$X)$J
.="<label><input type='radio' name='".h($B)."' value='".h($x)."'".($x==$Y?" checked":"").">".h($X)."</label>$Rh";return$J;}function
confirm($lf="",$Oh="qsl('input')"){return
script("$Oh.onclick = () => confirm('".($lf?js_escape($lf):'Are you sure?')."');","");}function
print_fieldset($t,$Ne,$Oj=false){echo"<fieldset><legend>","<a href='#fieldset-$t'>$Ne</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$t');",""),"</legend>","<div id='fieldset-$t'".($Oj?"":" class='hidden'").">\n";}function
bold($La,$db=""){return($La?" class='active $db'":($db?" class='$db'":""));}function
js_escape($Q){return
addcslashes($Q,"\r\n'\\/");}function
pagination($D,$Ib){return" ".($D==$Ib?$D+1:'<a href="'.h(remove_from_uri("page").($D?"&page=$D".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($D+1)."</a>");}function
hidden_fields(array$ah,array$Ud=array(),$Sg=''){$J=false;foreach($ah
as$x=>$X){if(!in_array($x,$Ud)){if(is_array($X))hidden_fields($X,array(),$x);else{$J=true;echo
input_hidden(($Sg?$Sg."[$x]":$x),$X);}}}return$J;}function
hidden_fields_get(){echo(sid()?input_hidden(session_name(),session_id()):''),(SERVER!==null?input_hidden(DRIVER,SERVER):""),input_hidden("username",$_GET["username"]);}function
file_input($me){$bf="max_file_uploads";$cf=ini_get($bf);$xj="upload_max_filesize";$yj=ini_get($xj);return(ini_bool("file_uploads")?$me.script("qsl('input[type=\"file\"]').onchange = partialArg(fileChange, "."$cf, '".sprintf('Increase %s.',"$bf = $cf")."', ".ini_bytes("upload_max_filesize").", '".sprintf('Increase %s.',"$xj = $yj")."')"):'File uploads are disabled.');}function
enum_input($U,$ya,array$m,$Y,$wc=""){preg_match_all("~'((?:[^']|'')*)'~",$m["length"],$Ze);$Sg=($m["type"]=="enum"?"val-":"");$Za=(is_array($Y)?in_array("null",$Y):$Y===null);$J=($m["null"]&&$Sg?"<label><input type='$U'$ya value='null'".($Za?" checked":"")."><i>$wc</i></label>":"");foreach($Ze[1]as$X){$X=stripcslashes(str_replace("''","'",$X));$Za=(is_array($Y)?in_array($Sg.$X,$Y):$Y===$X);$J
.=" <label><input type='$U'$ya value='".h($Sg.$X)."'".($Za?' checked':'').'>'.h(adminer()->editVal($X,$m)).'</label>';}return$J;}function
input(array$m,$Y,$r,$Ba=false){$B=h(bracket_escape($m["field"]));echo"<td class='function'>";if(is_array($Y)&&!$r){$Y=json_encode($Y,128|64|256);$r="json";}$th=(JUSH=="mssql"&&$m["auto_increment"]);if($th&&!$_POST["save"])$r=null;$rd=(isset($_GET["select"])||$th?array("orig"=>'original'):array())+adminer()->editFunctions($m);$Bc=driver()->enumLength($m);if($Bc){$m["type"]="enum";$m["length"]=$Bc;}$dc=stripos($m["default"],"GENERATED ALWAYS AS ")===0?" disabled=''":"";$ya=" name='fields[$B]".($m["type"]=="enum"||$m["type"]=="set"?"[]":"")."'$dc".($Ba?" autofocus":"");echo
driver()->unconvertFunction($m)." ";$R=$_GET["edit"]?:$_GET["select"];if($m["type"]=="enum")echo
h($rd[""])."<td>".adminer()->editInput($R,$m,$ya,$Y);else{$Dd=(in_array($r,$rd)||isset($rd[$r]));echo(count($rd)>1?"<select name='function[$B]'$dc>".optionlist($rd,$r===null||$Dd?$r:"")."</select>".on_help("event.target.value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($rd))).'<td>';$me=adminer()->editInput($R,$m,$ya,$Y);if($me!="")echo$me;elseif(preg_match('~bool~',$m["type"]))echo"<input type='hidden'$ya value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$Y)?" checked='checked'":"")."$ya value='1'>";elseif($m["type"]=="set")echo
enum_input("checkbox",$ya,$m,(is_string($Y)?explode(",",$Y):$Y));elseif(is_blob($m)&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$B'>";elseif($r=="json"||preg_match('~^jsonb?$~',$m["type"]))echo"<textarea$ya cols='50' rows='12' class='jush-js'>".h($Y).'</textarea>';elseif(($Mi=preg_match('~text|lob|memo~i',$m["type"]))||preg_match("~\n~",$Y)){if($Mi&&JUSH!="sqlite")$ya
.=" cols='50' rows='12'";else{$L=min(12,substr_count($Y,"\n")+1);$ya
.=" cols='30' rows='$L'";}echo"<textarea$ya>".h($Y).'</textarea>';}else{$nj=driver()->types();$if=(!preg_match('~int~',$m["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$m["length"],$A)?((preg_match("~binary~",$m["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$m["unsigned"]?1:0)):($nj[$m["type"]]?$nj[$m["type"]]+($m["unsigned"]?0:1):0));if(JUSH=='sql'&&min_version(5.6)&&preg_match('~time~',$m["type"]))$if+=7;echo"<input".((!$Dd||$r==="")&&preg_match('~(?<!o)int(?!er)~',$m["type"])&&!preg_match('~\[\]~',$m["full_type"])?" type='number'":"")." value='".h($Y)."'".($if?" data-maxlength='$if'":"").(preg_match('~char|binary~',$m["type"])&&$if>20?" size='".($if>99?60:40)."'":"")."$ya>";}echo
adminer()->editHint($R,$m,$Y);$bd=0;foreach($rd
as$x=>$X){if($x===""||!$X)break;$bd++;}if($bd&&count($rd)>1)echo
script("qsl('td').oninput = partial(skipOriginal, $bd);");}}function
process_input(array$m){if(stripos($m["default"],"GENERATED ALWAYS AS ")===0)return;$u=bracket_escape($m["field"]);$r=idx($_POST["function"],$u);$Y=idx($_POST["fields"],$u);if($m["type"]=="enum"||driver()->enumLength($m)){$Y=$Y[0];if($Y=="orig")return
false;if($Y=="null")return"NULL";$Y=substr($Y,4);}if($m["auto_increment"]&&$Y=="")return
null;if($r=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?idf_escape($m["field"]):false);if($r=="NULL")return"NULL";if($m["type"]=="set")$Y=implode(",",(array)$Y);if($r=="json"){$r="";$Y=json_decode($Y,true);if(!is_array($Y))return
false;return$Y;}if(is_blob($m)&&ini_bool("file_uploads")){$Zc=get_file("fields-$u");if(!is_string($Zc))return
false;return
driver()->quoteBinary($Zc);}return
adminer()->processInput($m,$Y,$r);}function
search_tables(){$_GET["where"][0]["val"]=$_POST["query"];$Qh="<ul>\n";foreach(table_status('',true)as$R=>$S){$B=adminer()->tableName($S);if(isset($S["Engine"])&&$B!=""&&(!$_POST["tables"]||in_array($R,$_POST["tables"]))){$I=connection()->query("SELECT".limit("1 FROM ".table($R)," WHERE ".implode(" AND ",adminer()->selectSearchProcess(fields($R),array())),1));if(!$I||$I->fetch_row()){$Wg="<a href='".h(ME."select=".urlencode($R)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$B</a>";echo"$Qh<li>".($I?$Wg:"<p class='error'>$Wg: ".error())."\n";$Qh="";}}}echo($Qh?"<p class='message'>".'No tables.':"</ul>")."\n";}function
on_help($mb,$bi=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $mb, $bi) }, onmouseout: helpMouseout});","");}function
edit_form($R,array$n,$K,$wj,$l=''){$_i=adminer()->tableName(table_status1($R,true));page_header(($wj?'Edit':'Insert'),$l,array("select"=>array($R,$_i)),$_i);adminer()->editRowPrint($R,$n,$K,$wj);if($K===false){echo"<p class='error'>".'No rows.'."\n";return;}echo"<form action='' method='post' enctype='multipart/form-data' id='form'>\n";if(!$n)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table class='layout'>".script("qsl('table').onkeydown = editingKeydown;");$Ba=!$_POST;foreach($n
as$B=>$m){echo"<tr><th>".adminer()->fieldName($m);$k=idx($_GET["set"],bracket_escape($B));if($k===null){$k=$m["default"];if($m["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$k,$qh))$k=$qh[1];if(JUSH=="sql"&&preg_match('~binary~',$m["type"]))$k=bin2hex($k);}$Y=($K!==null?($K[$B]!=""&&JUSH=="sql"&&preg_match("~enum|set~",$m["type"])&&is_array($K[$B])?implode(",",$K[$B]):(is_bool($K[$B])?+$K[$B]:$K[$B])):(!$wj&&$m["auto_increment"]?"":(isset($_GET["select"])?false:$k)));if(!$_POST["save"]&&is_string($Y))$Y=adminer()->editVal($Y,$m);$r=($_POST["save"]?idx($_POST["function"],$B,""):($wj&&preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"now":($Y===false?null:($Y!==null?'':'NULL'))));if(!$_POST&&!$wj&&$Y==$m["default"]&&preg_match('~^[\w.]+\(~',$Y))$r="SQL";if(preg_match("~time~",$m["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$Y)){$Y="";$r="now";}if($m["type"]=="uuid"&&$Y=="uuid()"){$Y="";$r="uuid";}if($Ba!==false)$Ba=($m["auto_increment"]||$r=="now"||$r=="uuid"?null:true);input($m,$Y,$r,$Ba);if($Ba)$Ba=false;echo"\n";}if(!support("table")&&!fields($R))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",adminer()->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($n){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"]))echo"<input type='submit' name='insert' value='".($wj?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($wj?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."‚Ä¶', this); };"):"");}echo($wj?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":"");if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo
input_hidden("referer",(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"])),input_hidden("save",1),input_token(),"</form>\n";}function
shorten_utf8($Q,$y=80,$ui=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$y).")($)?)u",$Q,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$y).")($)?)",$Q,$A);return
h($A[1]).$ui.(isset($A[2])?"":"<i>‚Ä¶</i>");}function
icon($Pd,$B,$Od,$Si){return"<button type='submit' name='$B' title='".h($Si)."' class='icon icon-$Pd'><span>$Od</span></button>";}if(isset($_GET["file"])){if(substr(VERSION,-4)!='-dev'){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");}@ini_set("zlib.output_compression",'1');if($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:Má±h¥ƒgÃ–±‹Õå\"P—iê“mÑôcQCa§È	2√≥Èàﬁd<ûÃfÛaº‰:;NBàqúR;1Lf≥9»ﬁu7&)§l;3Õ—Ò»¿J/ãÜCQX r2M∆a‰i0õÑÉ)∞Ïe:Lu√ùhÊ-9’Õ23l»Œi7Ü≥m‡Zw4ôÜÅ—ö<-ï“Ã¥π!ÜU,óåF√©îvt2ûëS,¨‰a¥“áFÍVX˙aòNq„)ì-ó÷Œ«úhÍ:n5éç˚9»Y®;jµî-ﬁ˜_ë9kr˘úŸì;.–tTqÀo¶0ã≥≠÷ÚÆ{ÌÛy˘˝\rÁHnÏçGSô†Zh≤ú;ºi^¿ux¯WŒíC@ƒˆ§©kÄ“=°–b©À‚Ïº/Aÿ‡0§+¬(⁄¡∞l¬…¬\\Í†√xË:\rË¿b8\0Êñ0!\0F∆\nBîÕé„(“3†\r\\∫çÅ€Í»ÑaºÑú'I‚|Í(iö\nã\r©∏˙4O¸g@ê4¡CíÓºÜ∫@@Ü!ƒQB∞›	¬∞∏c§ ¬Øƒq,\r1EhË»&2PZá¶iG˚H9Gí\"vûßÍí¢££§ú4rî∆ÒÕD–R§\nÜpJÎ-Aì|/.ØcÍìDu∑è£§ˆ:,ò =∞¢R≈]U5•mV¡kÕLLQ@-\\™¶Àå@9¡„%⁄Sùr¡ŒÒMPD„¬Ia\rÉ(YY\\„@Xıp√Í:ç£p˜léLC ó≈ÒçË∏ÉÕ O,\r∆2]7ú?m06‰ªp‹T—Õa“•Cú;_Àó—y»¥dë>®≤bnÖ´nº‹£3˜XæÄˆ8\rÌ[ÀÄ-)€i>V[Y„y&L3Ø#ÃX|’	ÜX†\\√π`ÀCßÁòÂ#—ŸH…Ã2 2.#†ˆãZÉ`¬<æ„sÆ∑π™√í£∫\0uúh÷æó•M≤Õ_\niZeO/C”í_Ü`3›Ú1>ã=Å–k3è£ÖâR/;‰/d€‹\0˙ãå„ﬁ⁄µm˘˙Úæ§7/´÷êAŒXÉ¬ˇÑ∞ì√q.Ωs·L£˝ó :\$…F¢ó∏™æ£Çwâ8Ûﬂæ~´H‘jÖ≠\"®ºúïπ‘≥7gSı‰±‚FLÈŒØÁQÚ_§íO'Wÿˆ]c=˝5æ1X~7;òôi˛¥\rÌ*\ní®JS1Z¶ô¯û£ÿ∆ﬂÕcÂÇêtú¸A‘VÌê86f–d√y;Yè]©ızI¿p°—˚ßcâ3ÆYÀ]}¬ò@°\$.+î1∂'>Z√cpd‡È“GLÊ·Ñ#kÙ8PzúY“Auœv›]s9â—ÿ_AqŒ¡Ñ:Ü∆≈\nKÄhBº;≠÷äXbAHq,ê‚CI…`êÜÇÁjπS[Àå∂1∆V”räÒ‘;∂pﬁB√€)#Èêâ;4ÃHÒ“/*’<¬3L†¡;lf™\n∂s\$K`–}∆Ù’î£éæ7Éjx`dñ%j]†∏4úóY§ñHbY†ÿJ`§GG†í.≈‹KÇÚf I©)2¬äÅMf÷∏›XâRCâ∏Ã±V,©€—~g\0ËÇ‡g6›:ı[jÌ1HΩ:AlIq©u3\"ôÍÊÅq§Ê|8<9s'„Q]J |–\0¬`p†≥ÓÉ´âjfÑO∆b–…˙¨®q¨¢\$È©≤√1Jπ>RúH(«îq\n#räêí‡@ûe(yÛVJµ0°Q“à£Úà6ÜPÊ[C:∑G‰ºûë†›4©ë“^û”√PZäµ\\¥ëË(\nê÷)ö~¶¥∞9R%◊Sj∑{çâ7‰0ﬁ_ö«s	z|8≈HÍ	\"@‹#9DVL≈\$H5‘WJ@óÖzÆaøJ ƒ^	ë)Æ2\nQv¿‘]Î«Üƒ¡òâj (A∏”∞BB05¥6ÜbÀ∞][åËk™AïwvkgÙ∆¥ˆ∫’+k[jmÑzc∂}ËMyDZiÌ\$5eò´ ∑∞Å∫	îAò†CY%.WÄb*ÎÆºÇ.≠ŸÛq/%}BÃXà≠ÁZV337á ªaôùÑÄ∫ÚﬁwW[·LéQ ﬁ≤¸_»è2`«1I—i,˜Êõ£íMf&(s-ò‰òÎ¬Aƒ∞ÿ*îîDwÿƒTN¿…ª≈jX\$Èx™+;–ÀF⁄93µJk¬ôS;∑ß¡qR{>lû;B1A»I‚b)†ù(6±≠r˜\r›\r⁄áí⁄ÇÏZëR^SOy/ìﬁM#∆œ9{kÑ‡Í∏v\"˙KC‚JÉ®rEo\0¯Ã\\,—|êfaÕöÜ≥hIì©/oÃ4ƒk^pÓ1H»^ìèçÕph«°V¡vox@¯`Ìgü&è(˘à≠¸;õÉ~«çzÃ6◊8Ø*∞∆‹5Æ‹â±E†¡¬pÜÈ‚Ó”òò§¥3ìˆ≈ÜgüôrD—LÛ)4g{ªà‰ΩÂ≥©óLéö&˙>ËÑª¢Åÿ⁄ZÏ7°\0˙∞Ãä@◊–”€úff≈RVh÷ù≤ÁIä€àΩ‚r”w)ã†ÇÑ=x^ò,kíü2Ù“›ìj‡bÎl0uÎû\"¨fp®∏1ÒRIøÉz[]§wÅpN6dI™zÎıÂn.7X{;¡»3ÿÀ-I	ã‚˚¸7pj√ù¢Ré#™,˘_-–¸¬[Û>3¿\\ÊÍ€WqﬁqîJ÷òÅuh£á–FbL¡K‘ÂÁyVƒæ©¶√ﬁ—ïÆµ™ù¸VúÓ√f{K}S† ﬁùÖâM˛á∑ÕÄº¶.M∂\\™ix∏b¡°ê1á+£Œ±?<≈3Í~H˝”\$˜\\–2€\$Ó eÿ6t‘OÃà„\$sºº©xƒ˛xèïÛßC·nSkVƒ…=z6Ωâ° '√¶‰Naü¢÷∏hå‹¸∏∫±˝ØR§Âô£8géâ¢‰ w:_≥Ó≠ÌˇÍ“íIRK√ù®.ΩnkVU+dwjôß%≥`#,{ùÈÜ≥À ÉYá˝◊ı(o’æ….®cÇ0g‚DXOkÜ7ÆËK‰Œl“Õhx;œÿè ›ÉL˚¥\$09*ñ9 ‹hNr¸M’.>\0ÿrP9Ô\$»g	\0\$\\FÛ*≤d'ŒıLÂ:ãb˙ó4è2¿Ù¢9¿@¬HnbÏ-§ÛE #ƒú…√†ÍrPYÇÍ® tÕ ÿ\n5.©‡ ‚Ó\$op†lÄX\n@`\rÄé	‡à\rÄ–†Œ ¶ í†Ç	† ‡Í⁄ Œ	@⁄@⁄\n É Ü	\0j@ÉQ@ô1\r¿Ç@ì ¢	\$p	 V\0Ú``\n\0®\n –\n@®'†Ï¿§\n\0`\r¿⁄†¨	‡í\r‡§†¥\0–r∞Ê¿Ú	\0Ñ`Ç	‡Ó†{	,û\"®»^Pü0•\nê¨4±\n0∑§à.0√pÀ”\rp€\r„pÎÛp˚ÒqÒQ0ﬂ%Ä——1Q8\n ‘\0Ùk »º\0^ó‡“\0`‡⁄@¥‡»>\n—o1w±,Y	h*=êäê°P¶:—ñVÉÔ–∏.q£≈êÕ\r’\rëpÈ–Ò1¡—Q	——1◊ É`—Ò/17±ÎÒÚ\r†^¿‰\"y`é\n¿é å#†ò\0Í	 p\nÄÚ\nÄö`å àr îQÜ¶bÁ1è“3\n∞Ø#∞µ#º1•\$q´\$—±%0Â%qΩ%–˘&«&qÕ É&Ò'1⁄\rR}16	 Ô@b\r`µ`‹\r¿à	Äﬁ¿ÃÄd‡™Ä®	j\nØ``¿Ü\nÄú`dc—ÅPñÄ,Ú1R◊ü\$ørI“O Ç	Q	ÚY32b1…&ëœ01”—Ÿ í” f¿œ\0™\0§†ŒfÄ\0j\n†f`‚	 Æ\n`¥@ò\$n=`Ü\0»“v nI–\$ˇP(¬d'ÀÙÑƒ‡∑g…6ëô-äÉ-“C7RÁ‡á ó	4‡†Ù-1À&±—2t\rÙ\"\n 	H*@é	à`\n § Ë	‡Úl’2ø,z\rÏ~» Ë\róFÏthâäˆÄÿ†Îmı‰ƒÏ¥zî~°\0]GÃF\\•◊IÄ\\•£}ItêC\n¡TÑ}™ÿ◊IEJ\rx◊…˚¬>ŸMpãÑIHÙ~Í‰fhtÑÎØ.bÖóxYEêÏiK¥™ojè\nÌ≈L¿ﬁtr◊.¿~dªHá2U4©G‡\\AÍÇÁ4˛ÑuPtﬁ√’ΩË∞ê†Úê‡ÕL/øP◊	\"G!RÓŒMtüO-Ãµ<#ıAPuIáÎRË\$ìcíπ√Dã∆ä†Äß¢-Ç√G‚¥O`Pvß^W@tH;Q∞µRƒô’\$¥©gKËF<\rR*\$4ùûÆ'†Ûç®–» [Ì∞€I™Ûé≠Um—∆h:+˛º5@/≠læIæ™Ì2¶Çé^ê\0OD¯ö™¨ÿ\rR'¬\rËT–≠[Í÷˜ƒƒ™Æ´MCÎM√Z4ÊE B\"Ê`ˆÇ¥euNÌ,‰ô¨È]œt˙\r™`‹@hûˆ*\r∂.VÉñ%⁄!MBlPFôœ\"ÿÔ&’/@Óv\\CﬁÔ©:mMgnÚÆˆ i8òI2\rpÌvjÌ©∆˜Ô+Z mT©ueı’fv>f¥–ò÷`DU[ZTœV–C‡µT\rñπUvãkı^◊¶¯LÎŸb/æK∂Sev2˜ubv«OVD÷Im’\$Ú%÷X?udÁ!Wï|,\r¯+ÓµcnUe◊Z∆ƒ ñÄ˛ˆÎ-~XØ∫˚Ó¿Í‘ˆBGd∂\$i∂ÁMv!t#LÏ3o∑UIóOóu?ZweRœ†Îcw™.†`»°i¯Ò\rbß%©bÄ‚¶HÆ\"\"\"hÌ†_\$b@·z™‰\0f\"åÈrW®Æ*äÊB|\$\$¨B÷◊†\"@rØÇ(\r`  ÓC˜∏«(0&Ü.`“Nk9B\n&#(ƒÍ‚Ñ@‰ÇØ⁄´dó¸^˜∫Æä¸ £@≤`“I-{É0£‚\nñBç{Ç4sG{ß¯;zÆ©b˜{ —{bÉ◊ØÑ){B‡¡xKÅ¬¿≈á5=c⁄™â´yÂÓ&ÏJ£Pr≈I/áÉ‹ \0⁄‚V\r•◊âÌâ»=∏£âÇN\\ÿ¶=√KâË}XVÌxπäóµäÿ•äÀãx≤©d¯’ä€å*H'¶Œ¥∏ª{X∆=ÿ =\0Ôè8º\0æπÖÂ[…´ÜJÜ⁄tŸ˘OÿeÖπéÿ…ãËﬁ\r¯˝å† DX˝çß≈áƒ˝}◊z∞ìçæ†˘)çy'èŸ'ç√—èŸIèÃ(˘[çl(5ô`f\\¡`øî˘eó.lY(π=zó◊î!èY%hÄæOπ+ã˘ïó`Ÿô\"eì ÊÁƒóò∫ñKÚ˘•˛øØ£ò∏ˇñ†ﬂöŸ#êSôπEêIúYçê˚õ.H÷JtG∑óú`æåHºJ5ªÅÕ5òô~ ∏Ä6Cã•h¯òß˘XDz\nñx°ÇyshööùFK°c°zj¢ZÄY8(π˛%Ÿ|yüI´£ﬂëÿÉõ⁄Èe°˙Y°Xª°ôu¢⁄ ¥⁄iú]¶⁄c°è⁄M•˙;ü»ßë˘Ú>«°ÉöQ†T©¯¸˙® [~WÈ~Ÿûc›Çzõ©˙µçz•∫Ω¢˙\r¨:  \0ËrY˚¢x)Ç !™˙…°πK¶˙+ßz!£ö”ÄC+òö∞¥ŸÆ‚√Ø:›éß™ô§˙©¢Zgö˚~z4f•Ø	•:˜ç£ís∫”™óÍ+ıçx ¬ö%åªûõ=≥ôGñ€Iêf3?ò˙„é¯øµ+Y¥˙q∂@‡˚Gú˙·ôy∂ªoµŸ—¥€p\r™~¡{Wúö∂[Ö∑πÈÆyË:\0∆\\ªã∑;eπ€°∂YI\"∑∏zd¬òk©Zˆ|[uöÇuèœ+ò◊π9qºπnR ÀÆ•Bóòªÿ◊Åz|\rä·§Ñ˝Åk§^ªÄÓì™[1™€%ã.ìçpA≠2<õ€=ºÿ°ïË\$È;÷5ú)≥õm∏ú!ãª—XX˝∫ãY√x®5vT\\ÆQ¿%:¿¢>¿‡…õ€;∏õeí|/∑ïùy¡≈ß≈Wêßx◊†|gÆúäô”ƒC›∆\\âõ¸áº<çº9z\\Æ#.FV;8°ËNÕX7¯◊ Œ\"8&d5¨PÖ4Gj? \0‹?\"=ò≠˘HER");}elseif($_GET["file"]=="dark.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("h:Má±h¥ƒg∆ê»h0¡L–Å‡d91¢S!§€	çFÉ!∞Ê\"-6NêëÄƒbdçGg”∞¬:;Nr£)êˆc7õ\rÁ(Hÿb81òÜs9º§‹k\rÁc) m8ùOïùVA°¬c1Åîc34Of*í™-†P®Ç1©îr41ŸÓ6òÃd2å÷ÅïÆ€oΩ‹Ã#3óâñB«f#	å÷g9Œ¶Íÿåfc\r«Iô–¬b6EáC&¨–,ébuƒÍm7aéV„ï¬¡s≤ç#m!ÙËhµÂr˘úﬁv\\3\rL:SAî¬dk5›n«∑◊Ïö˝ aFÜ∏3Èò“e6fS¶ÎyæÛ¯r!«L˙†-ŒK,Ã3L‚@∫ìJ∂ÉÀ≤¢*J†‰Ïµ£§êÇª	∏óπ¡öb©cË‡9≠àÍ9π§Ê@œ‘Ëø√H‹8£†\\∑√Í6>´`≈é∏ﬁ;áAà‡<Tô'®p&q¥qEàÍ4≈\rl≠Ö√h¬<5#pœ»R —#IÑ›%ÑÍfBIÿﬁ‹≤î®>Ö ´29<´ÂCÓj2ØÓª¶∂7j¨ì8j“Ïc(n‘ƒÁ?(a\0≈@î5*3:Œ¥Ê6å£òÊ0å„-‡A¿lLõïP∆4@ …∞Í\$°H•4†n31∂Ê1ÕtÚ0Æ·Õô9åÉÈWO!®rº⁄‘ÿ‹€’ËH»Ü£√9åQ∞¬96ËF±¨´<¯7∞\rú-xC\n ‹„Æ@“¯Öê‹‘É:\$i‹ÿ∂m´™À4ÌKid¨≤{\n6\rñÖçxhÀã‚#^'4V¯@aÕ«<¥#h0¶SÊ-Öc∏÷9â+pä´äaû2‘cyÜhÆBO\$¡Á9ˆwáiXõ…î˘VY9ç*r˜Htm	Å@b÷—|@¸/ÅÄlí\$z¶≠†+‘%p2lãò….ıÿ˙’€Ïƒ7Ô;«&{¿ÀmÑÄX®C<l9Ì6x9ÔmÏÚ§ÉØ¿≠7R¸¿0\\Í4Œ˜P»)A»o¿éxÑƒ⁄qÕO#∏•Å»f[;ª™6~P€\råa∏ TùGT0ÑËÏu∏ﬁüæ≥ﬁ\n3\\ \\ éÉJ©ud™CG¿ß©PZ˜>ì≥¡˚d8÷“®ËÈÒΩÔçÂÙC?VÖ∑dL≈L.(tiÉí≠>´,ÙÉ÷ú√R+9iááﬁûC\$‰ÿ#\"ŒACÄhVíb\n– 6T2Éew·\nf°¿6m	!1'c¡‰;ñÿ*eLRn\rÏæG\$Ù2S\$·ÿ0Ü¿ÍaÑ'´l6Ü&¯~AÅd\$ÎJÜ\$sú ¶»ÉB4Ú…Èj™.¡RCÃîÉQïjÉ\"7\n„Xs!≤6=ŒB»Ä}");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("':úÃ¢ô–‰i1ù„≥1‘›	4õÕ¿£âÃQ6a&Ûê∞«:OAIÏ‰e:NF·D|›!ëüÜCyåÍm2À≈\"„â‘ r<îÃ±òŸ /Cç#ÇëŸˆ:DbqSeâJéÀ¶C‹∫\n\n°ú«±S\rZìèH\$RA‹ûS+XKvtd‹g:£Ì6üâEvX≈û≥jë…m“©ej◊2öMß©‰˙ÅB´«& ÆãLßC∞3ÅÑÂQ0’L∆È-xË\n”ÏDë»¬yNa‰Pn:Áõº‰ËsùúÕêÉ(†cL≈‹/ıê£(∆5{ùﬁÙQy4ú¯g-ñÇ˝¢Íi4⁄Éf–Œ(’ÎbU˝éœk∑Óo7‹&„∫√§Ù*ACbíæ¢ÿ`.á≠ä€\rŒ–‹¸ªœƒ˙ºÕ\n†©Ch“<\r)`Ëÿ•`Ê7•C íå»‚Z˘µ„X <ÅQ≈1X˜ºâ@∑0dp9EQ¸fæè∞”Fÿ\râ‰!çÉÊã(hÙ£)â√\np'#ƒå§£HÃ(i*Ür∏Ê&<#¢Ê7K»»~å# »áA:N6ç„∞ ã©l’,ß\rîÙÅJPŒ3£!@“2>Cræ°¨h∞NÑ·]¶(a0M3Õ2î◊6Ö‘UÊÑ„E2'!<∑¬#3RÅ<€èç„X“Ê‘CHŒ7É#n‰+±Äa\$!Ë‹2é‡Pà0§.∞wd°r:Yˆç®ÈE≤ÊÖ!]Ñ<πöj‚•Û@ﬂ\\◊plß_\r¡Z∏èÄ“ì¨TÕ©Z…sÚ3\"≤~9¿©≥j„âPÿ)QìYb›ïDÎYcêø`àêz·cûµ—®Ã€'Î#tìBOh¢*2ˇÖ<≈íOÍfg-Z£úà’#†Ë8a–^é˙+r2bâ¯\\é·~0©·˛ì•˘‡W©∏¡ﬁnúŸp!#ï`ÂçÎZˆ∏6∂1ê2◊√@È≤ky»∆9\rÏ‰B3ÁÉpﬁÖÓ6∞Ë<£!pÔGØ9‡nëoõ6sø#Fÿ3ÌŸ‡bA® 6Ò9¶˝¿Z£#¬ﬁ6˚ %?ás®»\"œ…|ÿÇß)˛búJc\rªéåΩNﬁs…€ih8œáπÊ›üË:ä;Ë˙HÂﬁåıuãI5˚@Ë1çÓùÅ™AËPaH^\$H◊v„÷@√õL~ó®˘b9é'ß¯ø±çS?P–-ØòÚò0çC\nRÚmÃ4áﬁ”»ì:¿ı‹‘∏Ô2ÚÃ4úµh(k\njIä»6\"òEYà#ÅπWír™\rÅëG8£@t–·ûÅX‘ì‚ÃBS\nc0…kÇC I\r ∞<u`A!Û)–‘2î÷C¢\0=áÅæ Ê·‰Pà1ë”¢K!π!ÜÂüpƒIs—,6‚d√È…i1+∞»‚‘kâÄÍ<ï∏^Å	·\né…20¥F‘â_\$Î)f\0†§C8E^¨ƒ/3W!◊ê)åuô*‰‘Ë&\$Íî2êY\n©]íÑEkÒDV®\$ÔJ≤íáxTse!êRYª RôÉ`=LÚ∏„‡ﬁ´\nl_.!≤V!¬\r\nH–kê≤\$◊ê`{1	|±ê†∞êi<jRrPTG|éÇw©4b¥\râ°«4d§,ßE°»6©‰œ<√h[NÜq@Oi◊>'—©\rä•êÛó;¶]#ìÊ}–0ªASIöJd—A/Q¡ê¥ê‚∏µ¬@t\r•UGÇƒ_Gû<ÈÕ<y-I…zÚÑ§ù–\"†P¬‡B\0˝Ì¿»¡úq`ëÔvAÉàaÃ°JÂ†R‰ Æ)åÖJB.¶T‹ÒL°Óy¢˜†çCppç\0(7ÜcYYïaç®MÄÈ1ïem4”c¢∏r£´S)oÒÕ‡ÇpÊC!IÜºæS¬úbç0mÏÒé(dìEHú¯ö∏ﬂ≥ÑXã™£/¨ïôP©Ë¯y∆XéÈ85»“\$+ó÷ñª≤êgdËÄˆŒŒy›‹œù≥J◊ÿÎ ¢lEì¢urÃ,dCXç}e¨Ï≈•ı´mÉ]à–2†ÃΩ»(-zÅ¶ÇèZÂ˙;IˆÓº\\ä) ,ç\n§>Ú)∑û§Ê\rVS\njx*w`‚¥∑SFiÃ”dØº,ª·–Z¬JFM}–ä ¿Ü\\ZæPÏ›`πzÿZ˚E]Ìd§î…üOÎcm‘Å]¿ ¨¡ôïÇÉ%˛\"w4å•\n\$¯…zV¢SQD€:›6ù´‰GãwM‘ÓS0Bâ-s∆Í)„æZÌ§c|À^RöÔEË8kMÔ—Ãsådπkaô)h%\"P‡ç0nn˜Ü/¡ö#;û÷g\rd»∏8ÜﬁF<3\$©,ÂP);<4`Œ¢<2\nî ıÈ@w-Æ·ÕóAœ0π∫™ìπLrÓYhÏXC‡aò>∫Êtã∫LıÏ2Çyto;2á›Q™±tÓ frmË:ßîAÌ˘â°˜AN∫›\\\"k∫5oVÎ…É=Ó¿tÖ7r1›p‰Av\\+û9™Ñ‚Ä{∞Á^(iúâf¨=∑rä“∫äu⁄ ˚tÿ]y”ﬁÖ–˘Cˆ∂∫¡≥“ı›‹gi•vfÅ›˘+•√ò| Ï;úÄ∏¬‡]è~” |\re˜•Ïøìö›Ç⁄'ÉÌ˚≤âî¶‰Ø≤∞	Ω\0+Wáçcoµw6wd Suºj®3@ñåÚ0!„˜\n .wÄm[8x<≤ÀcM¨\n9˝≤˝'a˘ﬁà1>»£í[∂Ôµ˙dÔﬁuxØ‡<\"Yéc∏ﬁB!iπ•Íïw¿}íÙ5Uπk∫∫ç‹ÿ]≠∂∏‘“¿{ÛI◊öRÖâñ•=f W~Ê]…(beaÆ'ubÔmë>É)\$∞ÜP˜·-öÉ6˛êR*IGu#∆ïUKµAXåt—(”`_¬‡\"†æ£p∏ &UÀÀŸIÌ…]˝¡YG6Pù]Ar!b° *–ôJäoïµ”ØÂˇôÛÅÔ¡Úv˝Ω*¿†ÿ!Èö~_™¿Ÿ4Bçê≥_~RBòiK˘åí˛`Áâ&J€\0≠ÙÆN\0–\$‡Ãè˛ÂC¬K úS–Ú‚jZ§–†Ã˚0pvMJ†bN`LˇÊ≠e∫/`RO.0P‰82`Í	Â¸∆∏d ¬òGx«bPû-(@…∏”@Ê4®H%<&ñ¿ÃZ‡ô¿ËpÑ¨∞ä%\0ÆpÄ––Ñ¯Í„	ÖØ	‡»/\"ˆ¢J≥¢\nsÜñ_¿Ã\rå‡gé`ãú!k‰pX	Ë–:ƒvÌÁ6p\$˙'«•RUeZˇ®d\$Ï\nL·B∫‚ÜÛ.ﬁdånÄÓ§“tmÄ>vÖj‰ïÌÄ)ë	M∫\r\0¬.‡ äHí—\"Ö5Ç*!e∫ZJ∫âËíÎ„f(dc±º(x‹—jg\0\\ıÄ¬ı¿∂ Z@∫‡Í|`^õèr)<ã(íàÑàÜ»)ÃÎ™Û –Ï@Yk¬mÃÌl3Qy—Å@…ëå—êfŒÏPnÑÁº®–T†ÚØçN∑mR’q≥Ì‚Vmv˙N÷çÇ|˙–®Z≤Ñ»Ü⁄(Yp¯â\"Ñ4«®Ê‡Ú&ÄÓ%çl“P`ƒÄ£Xx bbd–r0Fr5∞<ªCÊ≤z®ÅØ6‰he!§à\rdzê‡ÿK;ƒt≥≤\nŸÕ†ÖH∆ãQö\$QüEnn¢n\r¿ö©#öT\$∞≤Àà(»ü—©|c§,º-˙#Ë⁄\r†‹·âJµ{d—E\n\$≤∆BrúiT‘Úë+≈2PEDïBeã}&%Rf≤•\n¸É^ÙàC‡»Z‡Z RVì≈A,—;ë´Á<¬ƒÏ\0O1È‘Íc^\r%Ç\r ÏÎ`“n\0y1Ë‘.¬\r¥ƒÇK1ÊM3HÆ\r\"˚0\0NkXèPr∏Ø{3 Ï}	\nS»dÜà⁄óäx.ZÒRTÒÑíwS;53 .¢s4sO3F∫Ÿ2èS~YFpZs°'Œ@ŸëOqR4\n≠6q6@DhŸ6Õ’7vE¢l\"≈^;-Â(¬&œb*≤*ãÚ.! ‰\rí!#Áx'G\"ÄÕÜwâ¡\"˙’ »2!\"R(v¿XåÊ|\"DÃv¿¶)@·,∏zmÚAÕwT@¿‘  –\nÇ÷”∫–´h–¥ID‘P\$m>Ê\r&`á>¥4»“A#*Î#í<îw\$T{\$¥4@õàd”¥Rem6Ø-#Ddæ%E•DT\\†\$)@‹¥WC¨(tÆ\"M‡‹#@˙TFü\r,g¶\rP8√~ë¥÷£J¸∞c†ˆå‡ƒπ∆ÇÍ  é\"ôL™Z‘‰\r+P4˝=•§ôS‚ôTıA)é0\"¶CDh«M\nû%F‘p÷”¸|éfLNlFtDmHØ™˛∞5Â=HÕ\nõéƒº4¸≥ı\$‡æKÒ6\rbZ‡®\r\"pEQ%§wJ¥ˇV0‘íM%Âl\"hÅPFÔA¨·A„åÆÚ/Gí6†h6]5•\$ÄfãS˜CLiRT?R®û˛CñÒı£HUßZ§ÊYbF˛/Ê.ÍZ‹\"\"^Œy¥6RîG ≤ãÃn‚˙‹åç\$™—Â\\&O÷(v^ œKU∫—ÆŒ“am≥(\rÄäÔ∫Øæ¸\$_™Ê%Ò+KTtÿˆ.Ÿñ36\nÎcµî:¥@6 ˙jP√AQıFí/SÆk\"<4AÑgA–aUÖ\$'Îà”·f‡˚QO\"◊k~≤S;≈¿ΩÛ.ÔÀ:†àkëº9≠¸≤äÛe]`n˙º“-7®ò;Óﬂ+VÀ‚8W¿©2H¢UãÆYlBÌvﬁˆ‚Øé÷‘Ü¥∞∂ˆ	ß˝‚ÓpÆ÷…læm\0Ò4BÚ)•X¡\0 ¬QﬂqFSqó4ñˇnFx+p‘Ú¶E∆Sov˙GW7o◊w◊KRW◊\r4`|cqÓe7,◊19∑u†œu˜cq‰í\"LC†t¿h‚)ß\rÄ‡J¿\\¯W@‡	Á|D#S\rü%å5lÊ!%+ì+Â^ák^ ô`/û7∏â(z*ÒòãÄì¥EÄ›{¶S(W‡◊-ìXƒó0V£ë0À•óÓ»=ÓÕa	~ÎfBÎÀï2Q≠Í¬ru mC¬ÏÎÑ£tér(\0Q!K;xN˝W¿˙ˇß¯»?b<†@≈`÷X,∫á`0e∫∆ÇN'≤¬ëÖöú§&~ë¯tî”uá\"| ¨iÖ ÒBÂ† 7æR¯î ∏õlSuÜ∞8A˚âdF%(‘˙†‰˙ÔûÛ?3@A-oQä≈∫@|~©KÜ¿ ^@xÛçböú~úD¶@ÿ≥âò∏õÖTN≈ZÄCç	Wà“¬ix<\0P|ƒÊ\n\0û\n`®•†éπ\"&?st|√ØàwÓ%Öà‡ËmdÍu¿N£^8¿[t©9É™B\$‡ßé©¶'\">Uå~ˇ98á†ÈìÚ√îFƒf ∞πÄuÄ»∞û/)9á¿ôà\0·òÎA˘z\"FWAx§\$'©jG¥(\"Ÿ ±s%TèíHäÓﬂ¿e,	Mú7Ôãbº «ÖÿaÑ Àìî∆É∑&wY‘œÜ3ò∞ÿ¯ /í\rœñ˘ØüûŸ{õ\"˘›úp{%4bÑÛå`Ìå§‘ı~nÄÂçE3	ïŒ†õç∞9éÂ3X÷dõé‰’èZû≈9Ô'öô@á®áëlªfØıùÿQèbP§*GÖoäÂ≈`8ï®ëØû˘AõÊB|¿z	@¶	‡ùb°Zn_Õh∫'—¢F\$f¨èß`ˆÛ∫ÜHdDdåH%4\rsŒAjLR»'ﬁ˘f⁄9g Iœÿ,R\\∑¯î >\nÜöH[¥\"ç∞¿Ó©™\r”ÅÖå¬ïLÃ,%ÎFLl8gzLÁ<0kùo\$«k≠·`“√KP‘vÂ@dœ'Vê:VîÿM¸%±Ë’@¯6«<\r‡˘T´ãÆLE¥âN‘ÄS#ˆ.∂[Ñx4æaÁÃ≠¥LLÇÆ†™\n@í£\0€´tŸ≤Â\n^F≠óù∫•∫ä5`Õ Rèì7»lL†uµ(ôÅdí∫°π ‘\r‰Bf/uCf◊4ˇc“û BÔÏÄ_¥nL‘\0© \$ªÓaY∆¶∂∏Ä~¿UkÔv•eÙÀ•¶À≤\0ôZíaZóìöúXÿ£¶û|Cäqì®/<}ÿ≥°ñ≈√∫≤î∫∂ Z∫û*≠w\nO„á≈z`º5ìÆ18∂c¯ôÄ˚ÆØ≠ÆÊ⁄I¿Q2Ys«KãòêÄÊ\n£\\õû\"õ≠ √∞ácÜÚ*ıB∂ÄÓÃ.ÈR1<3+ı≈µ*ÿSÈ[ı4”mÏ≠õ:RÅhãëITdevŒIµH‰Ë“-Zw\\∆%nË56å\nÃW”iè\$’≈çow¨ò+©†∫˘Àr…∂&Jq+˚}“D‡¯º”j´êd≈Œ?ÊU%BBe«/MÇ∂Nm=œÑÛU∑¬b\$HRf™wb|ï≤x d˚2ÊNiS‡Ûÿg…@Óq@úﬂ>ŒSv†ÑçßóïÉ|ÔkråxΩå\0{‘RÉ=FˇœŒŒ‚Æœ#rΩÇ8	àZ‡v»8* ≥£{2S›+;S¶úÇ”®∆+yL\$\"_€Î©BÁ8¨›\"E∏%∫çÅ‡∫å\n¯ë–¬pæp''´pÇÛwU“™\"8–±I\\ @çÖ† æ áLnéÊ†Rﬂ#M‰Dµ˛qûLN∆Ó\n\\èíÃé\$`~@`\0uÁâ~^@‡’là-{5Ò,@bru¡o[¡≤æ®’}È/Òy.◊È {È6qÇ∞Rôp‡–\$∏+1é3€˙⁄˙+É®O!D)ÖÆ†‡\nuî<çØ,´·Òﬂ=ÇJd∆+}Åµd#©0…ûc”ê3U3ªEYπ˚¢\r˚¶tj5“•7ªe©òw◊Ñ«°˙µ¢^çÇqﬂÇø9∆<\$}kÌÕÚåRI-¯∞∏+'_Ne?S€RÌhd*Xò4ÈÆ¸c}¨Ë\"@äàvi>;5>Dnâ ò\r‰Î)bNÈuP@Y‰G<Ò®6iı#PB2AΩ-Ì0d0+Ö¸gK˚¯øÌ?®nÈ„¸dúd¯O¿ÇåØÂ·c¸i<ã˙ëã0\0ú\\˘óÎÅ—gÓ¶ê˘ÊÍ°ññÖNTi'††∑Ù;iÙmj·ê‹à≈˜ªç∏uŒJ+™V~¿≤˘†'ol`˘≥øÛ\",¸ùÜÃ£◊”F¿Âñ	˝‚{C©∏è§˛T aœNE€ÉQ∆p¥ pÅÄ+?¯\n∆>Ñ'lΩ§* t…KŒ¨p∞(YC\n-qÃî0Â\"*…ï¡,#¸‚˜7∫Å\"%®+qƒ∏ÍB±∞=Âi.@êx7:≈%GcYI–à0*ôÓ√êk¿€àÑ\\á∑ØQ_{§†≈«#¡˝\rÁ{H≥[p® >7”chÎnçŒ¬‘.úµ£¶S|&JÚM«æ8¥¿mÄOh˛ƒÌ	’—qJ&êaÄ›¢®'â.bÁOpÿÏ\$ˆñ≠‹ÄD@∞CÇHBñ	É»&‚›°|\$‘¨-6∞≤+Ã+¬å Üï¬‡úp∫Ö‡¨°AC\rí…ìÖÏ/Œ0¥Ò¬Ó¢MÜ√iZänEúÕ¢j*>ô˚!“¢u%§©gÿ0£‡Ä@‰ø5}rÖ…+3ú%¬î-mã¢GÇ<î„•T;0∞Ø®íÜDV£d¿g€9'lM∂˝Hà£ F@‰Pòãun¸tFB%¥Mƒt'‰G‘2≈¿@2¢<´eôî;¢`àı=LXƒ2‡œ‰Xª}oc.Lä+‚x”éÜ&D®aíÄ°Ä…´¡F2\ngLêEÉ∞.\\xSL˝x≠;lw—D=0_QV,a 5ä+LÈÛ+€|\$≈i≠jZ\nÍóD÷EŒ,Bæt\\œ'H0¡åê±R~(\\\"¢÷:î–n*˚ö’(°◊oÆ1w„’QÌ◊rˆ“√Eète”FïÖ\$ËS—í]–\rL‰yFÑâë\\Båi¿hêîhd·ˇ&·öáh;foõæB-y`≈‘0àÑJélPÈxao∑\$äXqº,(÷°ÜC*	ŒÎ:§/ÇîˆÈÆHG\"ÇcÄàCÅ¢°Q∏\nF¡‘Ñ“#∂Ö8Ì¢F:–£\0úÄOkæ‚D¸∆])õœötT8L·í®îÊn©`’Œ±|™HJ≥àÄ÷ úò \"“6¯{ã≠É¡?=I<HGc ≈§F“@Ü,C Åº@jÏâ\$Lü∑‚(ânE ëP¢Êjbøn„Œë´∂‰W· \r¿LqÈâËœ–sPHÄÍâùz\\V\$kƒ“ètr5ã,è§lö»ÿË<Ò'\0^S02∏0f -5\"acº\"3Uìp£Êì\"‹ò©%ïÆ\0'Zt\"96ëÃ9_ @Z{ô0IàÁ¨D¿ZE@ÙÅŒN√h`°\"Ω`†\0µÑà‡–…π(G√H‚ƒCh• ôIºÚf`@ZDπ\$)‚K·;Z⁄¯\0‰/ÈCëT>r_R@OÂ`1rÜT“®Ib\0Á*π8Ö†ƒ«Àh\$È_íp˘Rƒï\$Æ•Ni^ ™P/O)∏¬.≈πT6‹\\íŸî@TÄæ—rƒÖ`)¯ˆ¿T=‚n\0åÄ2ñúe´+Ä9 ¢\\Æó@•‰˙Çê>…PHè1	‰äy# Ù•r˙<∞a∏e‹KÑ€/êcêM@_.\09Ààìì®Ö‘–Å¨BÆ‘¡Ÿ0iÜÅéaÛ\nídeÅa¥%|S2êÙøÄÂ#Äèì∏nàªDè\$/π+EŒdëï¯÷_2PöÀ\$s,ok°#¸<â	≤A¬ƒër{BîŸÜA-Q4“§Ÿ\n™\ry˘!∆b‰±é´Ò·èO⁄ˆ@…¨¡ék§º Í±\"ßr‡ù*§›áåíY“Ä/Å»ë a0ÒŸ%ï.gE~∫˘&© 89îê·√#@M_ ¿î˝7K‰É∏J`ÚX)≤B\$Ø(	:ügâñn*˘|ÜM6PZÜ™HtÍJtqâCxÜ[⁄ºó‰·Öl=\nïÆ≈U3 f\\ÃîJÓP	,ô:…}TAªSYH(ê\n¢∏ÿI∂Ÿ≤ƒ!t(2U\"À\\ÁX≠^sÃ	∆ìa!Æ\nPrêà`…X3fnb•ï©‡ËJ˜¨‹&∏zÂzQSf £¸‰t°!T?‡9%Ä(QÉûB¯}6B∞kP\0Û>ıgî&~fhUúrßè,¢ p5Hià∆pÉÑÖ¢q…ö¸gˆVÁV¸œOgìWEJ8‚0GÏ‘akè∞’@N NMƒ‰∞U–Ux»™≠ﬂS¶x	¡‡	KÇ@c†1yÍ±Vlœ†¶¬CíìÇ2Q^rP6|˝I^M™,¶j%d›`‹´‡¸Fßœ\\#%≥|ƒCñø≠°7Ïã¢‘G⁄TNñÑä„˘iê´HôñŒQ≠O¶œ¡CÃyBí—\$±%T∞ãê*·>z\rùMM Kp” ÅêJ7O€∑È4Â%Úï\$§pé‡íÈ4î∞ÄÅîäÕÇ£ØE“™\"Tùı\0OÄ\0í’@>	rõO®]ö°¢x“}^•I⁄÷@  ≈∫qnÁÖ›0©Bb°»µÇI…(§M/˝;È¶ }RN\n°C£<êb≠P‘µu?¬=PeπCíôïÖL^'ÏS‘Œ?}4)å”S-’√1\r5S´OEÛSFú”ò©AOR+”ﬁô+vßÂ5¬&C)ŸêÆõKSDBﬂ≥N|E\rc⁄UÙY æ¿Í£V‰¯à?Hò)ÂÆü+sF‰·k∫LPW-¯,¸U:í&ô„t{ëÆVo§∑äJîl'®W»e74Xên GF™'ÇÆﬁ`Êê…Ccˆ±%IlÒjèu6£ﬂ»¬v¬U≥ZÎã\0*úö®N‘ü#ˆ§(ºà®n•-;|çï4´]X«Ó¡y'ú†è∞;ê›Z≈ëÒ) s9»¿òê%ÅÄR+\$¿∞	øëQﬁ‡(\"°_kXòÑë∞ùÅ¶ò\nM#Äù¶\"!p~:Ë*˙¿ô∞\$µ3Oâ∏ƒ∆ä™6Ω+ïÉ‡\nBû{1‡|H∑K<[`3Å#ÂÆF@ËÕ«ê! |©ÿä\0‡ó>ãåÆòà[nrMM˝+Ö·ÆmO_é2π—»Ü≈\0´e^	Ã7Z∏&ÍµB≈JË§ìh7QO%rf∆p†ûŒÅ‚÷û•mêÿ®‚æ√á¬4E‡l´˙¸+ï‡‰VÆ£iÒN SçZ‡WtÈ2W≈[;™¿v\"%Å≈\$^÷-(I\$ »S@R-&≥T„z¨ök(≤ñ	‰%R8ÏuY\0[9-¢»Œ(ı)EπËâ8°=^πÜ°¡Gò5#¡ºÄæ)ê1V¶…b\r]îNe;&ÃYõ`r¨ÍIßÿP›±‹À¡÷≤™†\0≈@PÁ7∞∑‚è0H™®√ÿçR≠xæ\0000C|‰n=®ä`–·TTøÿ\rEhON»¥¡'†“&‹tc©K áç‹ïU5ú˛÷ﬂ¬Œ√ıP3\\Óá‡2\"\0yÛ5¢V]ºç©6>–U!ç°@ÀhuÃ⁄(º\"E%07BÖΩ6ûºd·HN±¢ñëµÏij';@Ç’eÀMzlSfjKYñ÷çÛ≠Æ-uhÛâHñúØsmL@È–\"r◊j ∫Èj'l7	Úï(uëuã—EÂÅ¬ï∑e•aÜ@ÒÑ+çKâ:”ï¬%n´z†VÒ∑ùà—;‰[Ó_Vz_≠ïE‡„‚8Ü<ÖSbõ®ôã‹Õ÷6g¿º:cÉÕ˛¿7\nµ®≠Ï%Qèõ K°7Û‹ÆB€Îë⁄Òw®uπ5©Ï0ªî÷ö„ πy√ncnKôâ˙Ê¶T8Â ô˜s±∫W=+ó=K\n_[p¢Gøƒ∑C5¢¡÷√'€D\"Ñ›M<\":|Mq4ππŒfïs¡x	ÅqlÕ∞õÇQP”≤aOY◊E=˚ıÓ6nTÎñíñBtúhƒC\0pˇ◊@n£ŒD(a‹P∞\"ÑäÔã'ZNÖ‰€¨¢Æ\r¸LNXäg±ä<!wï∂∏õ⁄[˚ÖB)¥ß)~Ω◊„c¬xî‡vöi¬¶ˇq…¯ï∂òa§@K’7sßEQd√ΩòÔkÙù˜ƒ?\"⁄3û-\"U∆ù|ïΩ˝Ì¬Ô|21D>ﬂ≥‚]¬≠&äää\\hËT∆≥5ö\0`TzÅ¢·s -ºN£π…Ÿ\"Üfù∏NÂLUπ]n(D©(òÍ&%\"èe\\¨óO„…NÊIn€ø§î\0“–ÄÏ∆ï±ÿ˜@¡Ä—ÔV‰|RàMYC€Tﬂ¡˚ˇb‘UHp)¿Ä»S’s¿ q”iÅ±ñ`Z5vtÂùâ∏*·OO\nÒ(Ö£›÷ÎF‡¶ÿ58√!ax@Ä{^Pæ’Ω∏?´∞¿eh}\\≥j^2ÚÑLΩ,6¡.ÿN	KÖ%±ïﬂñuîèÑip»»!?≤läëÜ -5ÌwΩêÜK\"V»ÿ\\√Is¢œ2!ﬂ\$4∫5v\níê‡ËÚgr√ÚN÷Â}˜£;û›˝≠¬ù˙áÇÊW%D(pWaÎ\0°v'‡±6˙ÆVÍ´‘∆ø0W¿ÒÑE4“EUl¬8«LDÓÑ∂E¬<kOäÒH…ﬂDU⁄	`vS∑¨Lì√!DTMbnWVô¡Cdáä)ZeËüÄÅ∏ˆ:æ2«d8ö¶KÂﬁÑ˛4Æ-G¸bÕæwQWÊ30\r¸f\0 ,µ`Qhl±÷çŸ0ÀPı‡0h@\\‘r∑8◊«Tñå‚õú¬1`§&ˇåÃwñXÔ>»F?ëó|Pë*ÒM§qZ—Øå¨}ÜÀ0k`âú#¿’´cÚí'[«÷±Àç|s…IJòÓû\rﬁ„¨˚ø<Oa∆º@‘Wë¨u∞T∆∆:—ÛE^™≤ÉæÑ≤!kä–ˇÑŒa\$»>5Úñu_‰‚KcCQør-—ä‰'\r»iCÏéúüßŸ@8ŒSÑPS¡_Xgl“%£	¡n1r.<Öw_a…∫ƒ≥ËGh“4\nÊW◊ZìÔaBn,\\\0¨±ÅDUè\nbbZ'ä“·72∫çÕr€¬¢Æñ}øY>/¿w\\Y–`^7J´jåSá¢ïØÅ®S.¿ío%ÊJg\0GD,º∆È>7†πíRÓÑàπ0·πØ∆õ¯3ºﬂ6¯%i\0S™^Lú∑A‘ÿ\riÚ‰O<∫ô¿a phv[Ø{ú•á\0ÈE´^xÛù‹ºgñYzWŒyGûaªÁã:(î>CΩÄûˆ÷e\0„÷⁄])Ù3yts_aÄ7Á+·ÊÜB˙úCòeT∑ﬁfÇo≈PÄ€§’2E∑Cæ⁄v«>Ÿwˆlñz€*pÍY≤˝ˆ±q∞ôˆÿöQ‚p\nv[|qı“®E[—XiÄÛ¢ÏÆ=≤z(	»M€nç]7F\rß©Cs4|-} íòèƒø(NU£?,¿•⁄Ö˝∞Üê‚ÿç∫q	∏‚pÜq~¸¨ˇ ¶Í©Fñ¬%†88∑◊Èù¶á¢\$◊ﬁ∞ó[º±µrƒo!3„˝(Ü∞ÜógÜ∆Ù◊•pJ!È¡¥q⁄Z∞v?—¯c≠˝—L£ù7£–6Ë¸\$ámˆí÷qßÌ8l!√˘5≠Cö;Q,Å‘dﬁsFı-Oòßf√à¯\$‰Ñ6Õ%U®C∏¥f\"ÇÁe(j∫\rMt«FúÉËÎR˜x;n¶B\$˜πSSÙx'¢ıGˆ˛ÈôäM”	òÀ4Õ¨'köø~±◊#9e¥≥Y∫¢÷~¢ÏÎ≠à;fﬁ+ŒjºKÑ9p®…‘MÜ'Xå/rt≤\0’\\ÕJ%Q®›Ë∑Rá\r–≤O3§|ãÂØö˘◊¬œ±≥4ò›xFñ◊µs5E»‘ê;‘íWRí“JXõ ∂óJÏ\$˛¡wzOˆœ&«µ¡ƒzêkèS◊\nú\nNUPå‚∞.ˆª0¿îÖbdkÇüPÂÃ⁄	G6÷+B‹zá1ŒéhQ>sHvç≥√¬ƒQêŸ†Eÿpâ›M‰Ä)õÿ\nä\\å—û‹PzƒËÌ.s€Õ¬ g≈·)a~÷∆»•›!(!êGÏhr[≤*™Ñ£™Ó’¢Ö`îò~Õ\"!‚Oíøâ5πG3≈û*qkgBó,\$ˆ„€**1Äc.ªn	8®•\$d†¥±VSneãMiZ∂Ì≈7≈æg∂A˘5‹àΩÇ⁄\n˙`∂,â2∫«a¶“ØˇˆmMk ª¥ﬂ…Ø≤/-Å›6µ@?#`àÿ)„‘Ääha©¬ÜÒäÜ·)Vc∆]“_=†Rz\\ÔVRßµ=æÿ∑≥(-„otı\$‹•»\n˜¢âdSm≥yµ⁄f”©ŸN\r˘m(t;DÕ¡ˇp∏2§›∂≤√ZRl)–9MÃõ¿,/ìèYix™—k—è)í.§2@S^˙ˆÅuè⁄Âdä6§!ÀÅ>VBí‡ x<ï∏Kt06ÉâÚ@»å\nGÇA·P∞(˚™NbDï–K\nï\"µ‰cN¨¥\rƒÉ.pıÄ§'2LïádÖÍü≤µ—ﬂ\\LyßA=	ıƒDäÉm3ü%ƒ@åô±Ÿà°•¡8ÂqbSP\"‚ﬁ¢ô∆Æ/œDzÎC&ªO˚«\0007fÄ¬D^1≈XÅ∫/„É,\nÑ˜vÁWx%f)åŒ' ‡DêdQ@ôÑI(“ã7Yæ¬|…›∫AˇQ±∏D´ó⁄†e 8◊á7k)_ Ò@\"\"Ωº%‡}∏	°(ÃÎ1è1ÿçß\rı° „eÚÜ·?-…µHÅê&ÎÕ‰ıÈ\rL€Í‚Ä'ªe€Æ0‘T◊]Õ‘C!¿emNzÏ	UzˆÒ¿…àâ¢èSì‹úaf∂7òMÍ^CäD£ı¬(_ÔÏ√ú„‚#\"Ìdr5¶9±Ÿı81â÷hf®»≠·a_ó√ótZX\0ËUº≠ùÜ{2nn]æ†;FR˚≤!ä}>sÈÉHiÅŒy#≥¥Ö?\"≈§•ÁûÌê¿>{∞ÆŒ/?7ÓFÆÚYØ∞˙™?Ají¡.ÜUú!5`¬áH¿Êé\$r\0ùÓ'\næ\":.å˚d‘ÇŸô∆™ÌqŸR’≠ohı›>ÍüÃ{Á◊1Ç›+‰>ËÀ…∑tÜÕk%-DÏ=9 }ƒC@„8cmíHr∞Ô†¡W¿n †\0ƒé<(¬RR´8æç˙¥YV‡≈`Îpp‹.UÉe_`ÆÖ∞π^¶ıÅÏµõn^Á_≈R|ﬂrŒÖpâ7/!M5±Ï≈|Ö◊¿\n˚&¢F˘±VVzÇáO≠A÷~—à|∆õ∂–4N»íø¨’îÚ∏îgøyh-çøù\nN\"r\"≥Ù’GcÙs™ë©ÄDê'†XoŸß•¯ëOÑ{•ç{Y{Ø∆E¯=TäeÏZë∏∫˙ïÓ{\";ïH€—Xz§t±wÍ*-∫’ﬁıU®ÁËßw˙-˛§\"õ¶<A^øO∫ÕT ∂]ÉD?:ó˛˘Â˚©ÂÖÌÊ<ëÇpÑqı[øâ»,)©&`€{xKI¬I`∫`éŒc˛∞0É±˘™D«y8ˆá…qCñ≠YçÎCFıòÁêJçÕŸnk„[π8˜…¢Ò:\n^€÷Å´ƒTÿ!X*M˙<î5`\0Ø…6AÚ2o–P.µÈ£a¯AH®∂#x[∑óÜÄÅ‚ñûÔÀ 'êo@øÊO0^‰éÍ®Ûh|ﬁPé=+Õ)∫d[©«»¯X-éÙW¬!üÖ”Ë√Üî/:\"â0k#X«û<Ù‚∞ÙhÉCGâ›†@FÉ(ÈåkÜˆëπl¢&HΩF0OSzÖç≈wÊQó˝3›≈Ÿz|+éà\r9bΩT≈}'‹¨wA¥\r∞nF˘ã©î—!»g0älpõêl—1˚+¯|§hëkzó‘i&˜™uÎD±{K÷Ó\\æ†¢\$t(∂;Ë“‰è√¨˛™H˝r|BwßD3[M‚!:(›{åZÆÂ(|-”Hy0Í^ì'◊ΩÖ}Ôè*£¸“ˆNKéÖØ´ëä5KUõ≤·jMÂ\"Ö¬w·ñ]%¸˚ñ{1qŸ»z†Üü)]—≈Æ[kò\0O4ﬂ˝“Ï˚ìUF¿\0Ûc‚ìúmZEGtësDQZ„)n;7ê<íqhlXxßI∆¬^ÃVÓçÂ&ÜÕ∑—Cñ`,…ë%£°1\"@1«|Õ)óR•kﬂ˛VœÍ}S,ƒ#!…ÕGµÙ·]˝§ExÂ›˝YT¸˝<%ˇQ—ø€@⁄ÌˆêÖmÙ§∂JcÊÊôB£ãB ièúî‚GÒ«f2†äò®cD«‰n’ßß=Jç¸ÄI_∂˚ÇõöÓ'üÃÔÛiA†&,ô–{À˘c√⁄4∫«oVù%Ñd°2˝xÄeªÖë#s_U”HÂ’âWè!  =€∑œO˙<(y\0Ä.¿ÄGπ'œ\râèâ57‰pVÚ∫∂(Êø√æ:ÓÁ}ÙRRHHy[“ˇ	¥≤çø˝ 1êÂ¬¯O\")ÒÒL¶lê¿Ò1¬ˇè˚Ì«ﬁêâ´˚°ä+<~ô	\0ø¬Ás¯ØÎ?–B@ØÙÄdˇ„˝‰Õ?nˇâ~¡&L–Ñ≠†?´ˇ@:@;˝»yæÚQË∫>»Åâ„”f¸´˘:\0ºtÊ+j˛szÈKê,b^·p∑¿˝HX≈?ÜP¿\\DË?v\"£ÓÀ¸Ö\"¢&∞ ?≠˜Øªát˛õ`·V?´\0ì˙‰JÑwC1OÑì#Í∆êÉ*	˚˛@ÃøÈ\0√˛¡∆áã˚°/#8\"¢O≈\"•\0Ä„°¯6êNcÏ√§ê[˝p@CÛh\0{\0	æpDO˛¿Ft£»H/!h@ÊˇL∞;¿@ˇÏ¶wê¡ÙIê‘~CÎÀÄ¬∏)ÓE°©4+ºØ∞)îß·EbÁ?]´d§Ìë\$‰<§ÈáÃ`o∏æ“£ÓÅÔ?}∞8∆bæÿ∏/∞J™ßŸo#Úº⁄IV,Ac§¥3ÌXa ‰»oÓ™xiÀı£\"Ê§åCU¡™ÇD∞kàY»äÈ}©\n\r\0,G∆\0 |qªØ Ç.≈äÄ∆¿NêqƒpNÜ–îíjBO\$|Cıp}ü∆¬É4`±¬¿\\*4÷–bA§‡Û+ÊD_ÙÚ¿ÉƒôX°\$åÇ∑ãÑ@ú¢6\n\0\$Ö~À£Ê\0¿ÆJb›Ö°ú¬ UÖpîXıiD\"¸€éÖÁ†lg—t'Å£˛ë Á+x¬<ê®”Néﬁ51e‡í¬0`ÚøÒB8qﬁ\"O-‚Ää	C!¶“öÿm…µÉﬁ⁄ﬁ*∏∏f@#é6ÖZ–õ9†§îZR‡«Å∞Í∏≈¿„	HZLÄ eÚΩ¢˜Ó9¬9ú¿ T nÄŒ?xX\$Óùî0ì¥%\0002Ä\n¡yÑ!êöe‡:\$»QssAµûnxKÅ¬Ál1'†ÄNz!p•¿¨.·πÜÍcÈpæì§1@ãÖ)mÕ:@P¬\0·1\n‰(CR‰5D(ºäîPÃ1#	›d7í+\nÇ£Bu¯ëhaçM	aÓ\0î>∏1W®˝°\0aòæ4 s“-◊Ç'ëjp´ãÂ\nJmQ®˛â»)†");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("v0úÅF£©Ã–==òŒFS	– _6M∆≥òËËr:ôEáCI¥ o:ùCÑîXcÇù\rÊÿÑJ(:=üEÜÅ¶a28°x∏?ƒ'Éi∞SANNë˘xsÖNB·ÃVl0õåÁS	úÀUlÅ(D|“ÑÁ P¶¿>öEÜ„©∂yHch‰¬-3EbìÂ ∏bΩﬂpE¡pˇ9.äèòÃ~\né?Kb±iw|»`«˜d.ºx8EN¶„!îÕ2ôá3©à·\ráç—YéÃËy6GFmYé8o7\n\r≥0≤<d4òE'∏\n#ô\rÚàÒ∏Ë.ÖC!ƒ^tË(ıÕbqHÔ‘.Öõ¢sûˇÉ2ôNÇqŸ§Ã9Óã¶˜¿#{ácÎﬁÂµ¡Ï3n”∏2ª¡rº:<É+Ã9àC»®Æâ√\n<Ù\r`»ˆ/bË\\ö†»!çHÿ2S⁄ôF#8–à«Ià78√Kë´*⁄∫ç!√¿ËÈéëàÊ+®æ:+Øõ˘&ç2|¢:„¢9 ¡⁄:≠–A,IÒÃv4«¢˚ÍÜå£òP-´\n“∏Ø®ÿÀ%>(‡¨c(PãÉê∏74c8X–Ô`XÖ‚Ûî:\r£‰®3èä ŸKIAHHÖ»sÎ\"N“8R≈0HY5GÉDπW(Æä„π3¨åØUt¢åÍ  Pﬁ9Màê¬˘Vd˚?å4\rCêP™Öbÿº2*b‡3ÆT`‹ˆn®VMïsb†à0]pGµ%nç\\£Eœ]¢8ﬂãÌh∆7µ°E`ç÷è@PIÌ•jVΩˆTˆÌzâ\rC+åÑ∏R8\rÛ\0aâRÿæ7å√0Ê˝∏Ω˜l_∂2dYAxPZA€∞ä@yÅ∞ARÖÙT »o®‰^CK~còÛÙÈ‚ä∞{}c∏¯Ë„Z.ùõÖ~Ü!–`ˆø¡@C´.ûáÇﬁí.áπÙ®Èπ§˝yÙ°\nÚlˆÈ9wt\\C\$p’®p…Ÿ8Ê/¡Â™§eyn_ò≥ùßöÊ„‡HÁ!fwZÙı%hˆÖ∞èc5~[√H{\$ªÓ\nµª\r!ˆÙ4Ö°nƒˇÏn6ÕäÖcH∫ÈÁ€J.6É|`”õ˜;.ìﬁ∞[óã„˘pà ‡°¿Wõ›™›Ù>˘˝\\˜÷ÓíhW™ÙZæ≥≠ÃO‘ 7P˙ˆÃxAæpUWÒ)´µîÄÁπ!à/áp“i”[¡¿Ö¥≥~ãX‡\nR‡Ù˘≥‚\$¡8?BE’y!cÙÜP⁄C·ù¢5.\nH±]=´y*\$¬Èñs©Å–¿tö`°´5¶º7a¨\r\0Ë5«jÃ‹-gÃ¿˚ò∫ﬁ\0ıÕ§#ìïÍoA–√–ÓÇ\"p¥;£Ç\nH<ÅÅïπ∫—m!°åÉ≤†ºd√ôóK¥>+dÓ†Å¯=°p)™pP	#¿|´<)ò70ì€¨-ª„¿ó·(eká˛9HÈ EèË9ÜÄ∂ÃÙùéíú.É†N¨‰îíƒJá êhL>e<€øºCö`K¥ÈxVAøò ˆaêP–A9WîIÇyã4WjÁpÖW´•’¡d≤ER–2Àip#)Å—Ã¬ÿ⁄›CD?Är∫u∞â™xsó≥|œ∏úAX+?ß˛lë¬<H†&–÷ÓÒ–T#§|ê–†Q£b ∂-\$∞}Ahù:t0ÌPˆùD®9!9SmÇ¬H˚i\ro}àøà∆™èP_éE—aøÊx≠fö∏uíÅ{ö”≤v‡‚<)¬/#—QC*‹™\0∫rNirÍ“t⁄GNo§w>íÿƒ¿µM‘”ºáÚ DJÜπCv`Ú`N˜a@]∏(òU Û¶˝S5{í»=Ôÿ∑â9N¥â”Á8zçô3Ñ^<áª	Î§—	†X¢c•\n=@¸øsô3&âÍö†äd•˘òªAj%\rÅ¿y\\{<#·ö	UöógœR`Ç§^¡õK4lÂø!˜t∞º¥{Ö\0û‹W´&Éò|-‡ÉÈ¢U¨¿/7yU∞ C∫øÅŒ–X™œR°6uÂHÜ§ÂVâu|I†Vß’\nq<Èºá*p˜Å)Ûæ”¸ˇ©&NÇ¯q°º/ùRŸÑ\nV	∏8©≤ïáç˙√·¿3á<;Ü©‘¡ƒ¯}_¨ìÉäph\r¯†• ”äpt¢9#%<®æ2i‡d3ÊRÅñsπ\n·€¯kOf»«”‰´9pAï\n ‰∏9É ∑Ç˙º Iî˘YÃ»Ú˛C¨c,UÑ≤î2Ê^Ã\0Ì0\$ˆN¿ÆqsJŒ+d∂*É@1:u¬∂ÜÎ¥œáÙ˙ÌkŒÜ©!”4;È@zöZë«&§Ãd\n3\$ï†≈ﬂ›†C®]¶§˙£Q ›BVwp™.KÃ\\Œ¨‘å\$9¿i<2Zp:aé`U‘¥¡Ô÷S®3§ê›|T!º&PÈ ˆ,c=¥ƒ0”=à«ÀNã÷€díÎõ≠6nœZyiTTJ∂®w⁄˚eSÓu»'ön≈mÌã∏I®n\r;êó›îü≥¬Ñ›*)AÖ„iåÉô1öyQÌ\r€_8?‚’ûæÆ7•6ñ⁄¡Àl1¯«ΩÇ˛˘ﬂ{ÚôΩø±∞Ç‡ácÉ≤Ü≠vrä„˚ª{\\ÆŒ.ó,€ºªﬂeÍv˘òk‡€õeÛ~L˜^ìÁù7ÜÖ¿Æ\nÅ@.sÌˇÁ›8tñ}…ò8¶CÇ-‰õ—ªÙ-˝∏4ﬂIÈdO{sç’ª8‰ñ‰[ÀµÚf∑;}Qƒ¡≥πs^›π◊Q⁄2[™(@ƒ\nL\nÅ)¿ÜÇ(AÚa\" ûÁò	¡&ÑP¯¬@O\nÂ∏´0Ü(M&ËÇ}ö'ö! Ö0ä{6ûÒﬁ}˚∫ïk˜ ò@;px6ù·zg÷|+úÇÃÚDÚÓ‚æ+ã¯œ§ yJäﬂL#ç}ŒÛ¨~˚¸*/}ÒÔÕ»4∑¡‰|ïAw˝˚Û<¿ÑËwOÒ¨Ë‰X\0¬Äƒ‚ÉŒ’Á~¸Æ˛\rﬁ⁄èÊﬁè›≠‹˛ŒZÂÏ®ƒ*¢Ÿ\nœß\0v‰0 ÔèË‰Ô˛*ÕÚ˘/óhD‚?O˙\rnÍÈBÔPF¯oÍÌœÒ0\\ˇ`Á0f˙∞kÔÑÔ∞r˘OÔHpÄˇh˝ÓxÔpqœ“÷êP·áT†b¥â†∂ÂOPîƒØı8Êè¢ÊÕ—P†⁄O˝o∆.ŒÌ0ßŒá\0∆\r¿Õ	∞™˙éÓÍPE∞KèÏÆïÕô\rP)\rêˆ‚oƒT˛Ëv Í\rÇD‹Ø˝∞üo‚ˇÌ¸MˆA(XhCÇL&∫õ\"h\r,“N¬^qKkb†∂ÿ\"Çë	«}qyê\"Ô¿RÕ`˙∞ƒ\0î∞∂–≤∫Ünõ+î¥Æ\rn°ÑÚ≥qH´HLÒÆµ\0Võ%äüF: ÿéÇΩå\$\rÒ¨ÉÇfÈ¨∂—òjêBÁm©Qm£G\\ûËï±ò¶Ñnk´í%\"VΩ±d¨¬k†Ç@‰ Ú™ÄÁ!2+6∑“%ãÉ ß~î»Õƒû%Î r.ÃR[» 2?\"Ãπ#\0∂‘Äw\$¬U%±#!%≤)\$Ú	\$L»mA-W¨»{@‹∑¨ﬂ#“_&Ï‘x“‹Ú]\$S'\0‰\rÑÚΩ„íg…@mËπ0°`dÕf∫`G&L\0»':xÉjxÁì*–æÏD»L«‰≤ÍË¥ƒ∂±∫ô≈ﬁ(±¿q≈ã∞¬,&‘û‹Ôl¿Nt*†\n†§	 Ü%f(á£ºœ–æµkZò	Ñ∂à%iÆn\".ûÎƒª«Ê∞Æ∆~\0ÊU@ƒ§dÄæ4ƒˆ'r§\rn#`‰Ï2H¡ ∂Õgä6Î&ú£v†∂∫å◊'¢\rrÄÇS^î\$Âö@¿ÃXf>ŒÉk6√r7`\\	ù5àVã'W5‡∂\rdTb@EÓ£2`P( B'„Ä∂Ä∫0†∂/‡Ùw‚êës⁄≥ﬁ„&r.SVs—î9…JJÚx&ó8≥é¥Äª”v¿‘!`z4\$k¥\0„–xö7pIÛ§ ”©AÈ9µ;¥™ÄŒ\r≈~ØËØ4ØÛ>~'á\nPåås0P‚ÌQA+/7`WOÂçÈG1ëFpÊö¥\n|Ì\0PπGãGtÉI\"TÌiG†O@∞ΩF‘V~GËçî2ÿ\$ªÈ™%∏´96¥,7L–÷Ê—LSoLíhÕÛP5 ºÊ–£\0§†Œ£P¿‘\r‚\$=–%ÉnUjXUƒ‹»k‹œã‡N\0Ê´Áî\r¿æ)FÄ*hì@ˆk†B≥î⁄5\$ò´56Lbs|Mo8+8\"ı:ÕÛG4≥ONìS5ãŒ#j≤\"Û≥NnßÆcÁJtÂΩT¢%(DüUìS’]M’j\$TK`í5Ñˆo@˙âË≤»ÃÕßrYSNR1ER÷\r¿∂≥ëæE≤XrÙNJÜ7’ìbãìgTUxÆMç5´*Ó0r’:3¶≥	Ùô	ï2iúö1Qä¯µkºFÂº–0éïYZstÕeºΩï¶c\n:oH FE£†xuç¢Õ#Ñ˙4„S#	 	\$®t?ı¶E(pïÂ(ÍR\"|eB†X¶ÉÍ8	4≈>\r/¥<Ì\0E,^ÁD.ÄÀE{5†€aµ‹Ü*‰–\r—‡ZâªgÁ|‘÷~÷\r:moc‘—9ı®ÕJ¯v*åÙ√B¥“7rT’&–≠nlHé∂∆PV 6‘«mDw»)m†ˆ\rµÒCV®w„˙†\$˘uüSÙ∞”wS`ADÄËLáS6qàk≥ä)Jklé'L£hB9hñå Jimn<\0–  Ç<Ê∑\0æ[Öº:\0ÏK(¨îç~™ïòœs\0˜KÃíˆØY' àgŸaÁƒOá¶∏Ç¥ÿ(∂ó]vë:¶&!`ÌP‰‡xV^wÇ≤∂†n∫ƒπ‡¯7\0æ&åg|B\0(“¬”Ï*,¡◊ƒæ◊¬≤d∫ò7‚õ¨t«ˆzíw•zå\nªE\",\0‘\"fb§\$B„(Ûh(Õ4’™5b?√Œçw¶¡q|@∆ò+çÇÎÿÅÄﬁ∂◊Ù∏&…ä€~N‚¥é‚ïÃ◊ó¯N6<u¶FxWQµ¿^¿^¶øß;P.#/≠ÇÉÁ|W»É8k.’≈/7K/w»Ql¡8É~Qœàä≥\\1√\\òìÃ&\"ÿ¶WRÔâÀ/å)|êæA5rßµeEÉ@æ¡kµà\0O‡ÕwK&◊fÿ”\"'Lmé∏‹l@˘¯€ÑPZ≥˘„˜ê7™»ƒ\rï#ëoûÿxÜ`]ÅƒbÃÑNzZ@æ0NRË,ÈÜx[P§πÖ¯cÅï≤8zìX»\rÜ?åÛ«çÃ?ä9˜2√xá}ÄL¡ÃF'LPyz√∞\\∆ô«åT√Ã ≈§º¨ièN«Ê«Ä¬«◊“Tx%Öxauícwí∏#l,áå\"‡Pà£€bî*∂õògÜ#ZudÕË,5\$¢D§‰è3]áÿõ?àh~´0\nΩyÊN7∆bò¥Õ˘˛zã\0ﬁa5qïòÃk∑p√˜våìí±QôµÅ˘,Dñ[úπA\\EùyKÜyP#U°πZkπÛà&)èòEä9qö∏‹Ë¿Óø\"™7ùπ≥íò!£Ä⁄[ôÕQô–Md€îöuQöJ#\$oåπ]•j€•πgâ¿O¶\n¶XDËÕ6…Í£¢ÿeÅπ∑ß∂X«ZÅ£¯ß§:ç¢ÂE©:O©öU©Ÿb¬z]ë7sñ˙´õÿD√ÏcÉ£0π`¬?¢ñ\\÷S{›yıØ»ÈØSâih—z≈EiÁij&ëÆ◊´e'ºk∫≠ìéXíy f6V-ZÎÑWew≈ä;GÑ\$·¥◊Âà{S¥∏ûKóŒ 7	≥1n∫ó>@Ãiz˙√zˇw´9†˙âõ{†x;ü∫ö\0òÈ∏⁄\nI˚ÖπöÌykãâû[•û©7{∑ﬁª8-~óƒ”wÒ,[l»åƒ@œ∑ïí†V‘ò+ä·π”ãòèäÿø˚âΩjΩ€c∏ÿ§Õ¯©\\q«ä˚ùàπYæºæÏ'∏∆§zΩ£Yª¥ò›ª´çªÀúèúô?aöAù:ãQŸ≠„ù˚Ê(•‚} Ûá\n°áyÓ#SÛy\0œ[áÏ?‡Œœ»/°öó°Ÿ]º™¡’´ôM£y£{À£‹9¨º=P⁄œ´πOÜöLs\\sWD¡¿ÿªæÀ±á|7úÒjN-àE†Àï+á`uû∆º°\rM}◊Â~øªÿIô¯’~i¶⁄¥ö±é|Áólv√˘}œYÄƒL1òl>\rπä≈ê≈˙Ò±9ñ‡,o¢Yü–9£}â¢ô«’Sggøå¨ÖªÈäº ËÛÀ:ÖÀu)À‹¿Eº≈ÃÄCÃ¿¯R%ª∑Î~|Ÿ~ÃwÕÎ≥Œ0]Œ|Í«\\Ó√yœôå√yò\\ˆ¬ÿ¨7–πï–Ïe—,môÃuö•“7÷˝(T],wÒàŒ∏fU=ØÖöÇTRW6ñ<÷Î“K÷Ω∏öæg⁄;Ù≥À¶||1∆\0QyÆ\"9˘vb\$5∑mwˆ≤ŒÜóoïË\r\0xbÄkHòÈ|µ…ö ¿Z\rÎhª¿W ú\\¶´‘±±‘ˆ.ÖÛ3Uˆ\rÀΩÿò\rΩ«·>?2)ó·©ü‚/‚ù=‚ﬁ5À˛0@∆ÖH◊~<ü–Ω‚x∑àﬁ_ä˛/Àæ3Ê~I+~l~çHâY‰…{Â˝·ﬁY‚^]¿^a„eË^hïèÎ^r+>C˜π’bBØ,∂˚¥´2/L Ë≤¡†ºR˝#mµRKIÄKà'ÌîïEˆW≠1Ô]Fµz¥_]ÛTﬁ—%4Ãî\0⁄V=Ì4·;\$Tí «Êûç{üØ?Ê†˜Ô¨º‘ûÛ3¿˘n\r¶z º˚X?cßpó\n?˙#ÿ–aÓd°Ó§∫µ”X¥\n’«:z‡Ã-Ç^XÏ!†“`¯:\0‰«ˆy,Dl„’J`˚¢A)h’UıÈµ˙µıˇÍ+ëÁºÁÒÀË5+Í¸√ÊÁ~_…âÒ„˛ø®ñπ+<πb]<m5é~'óÛÙüóˇ]π˘')Ùﬁ¨Ñö‹∫/˙ôΩPúærËú4”oı{ãÙ_òngø†HF»pBs‹H˚1)ûÓbÛïﬁbÒß ?ïÌÜº\"[÷C<˝U~<0∂⁄y„Ä:ıG†@}ËÑ¨zÿÔﬁ∫Úw)}°˙[ÍñûÙÁÏ<8ö&∑X\"`›B≠Ww≠µ{ç≈k˘îU≤ùøΩ¢Ä.˚„‰ßE;¿=œpQ…¢≥ÛR)t\0;¯°‘º“Œ*≠ÜJõC^ §dìÎ,˝+d-®Ä~∏*øxpnÇú@˚•AÒ?ŒQh{‰Ñ≥'A5ˆP{dXº`ﬂH+îãÍsS™ù≈kX/íîE(3=®!00ò4¶í\rj≈Ç–Za€Ù¡¡>åm˙≠›4°ªæ¿?og3x∆ï˙JW\$∞EQ¿íË^&Ï…\nQE©ïﬂhØ“jÄËï√qCÑN¸è∆†,y·ÍHôõÃŒ≤\$'@\n∂˙;\0\\]˜œõ–≤(È\n6arç«©¿u°P‰/Ú;Pº#q1¿ıÀ\n£PB.‡6©ı∞Ñ`\n◊FŸ∞àÕíWªªÀ¬˛¶†3dbêZU∫ƒ÷úÔ±=®¯◊õxÿaˆ@ê=ÆÇçÉfÀ¿ÕZ¶≥;BëkË¨Ä¿ñÔ≈ÎmJéÓNôgÃ^¢ˆ›pÈr≤πù‰Ÿ≤Ø(Ilcá¢É˙¯éØp*ˆáåAü“O·´U¬7\\D<Tˆß‘f+†THƒÀœ†`éR«ÙÇ¡Zqí[`of\\üà¬\"ÄœÄx“|Eâ—fÄ¢∆≤·∫≈∞P/áS\"≤_Œ8≈-CˆFÙ]\"j„hÆÿF˘29¿È!E˘”ÂÏb[ã‚Û—¯Eˆ*–ÍååMÏx¬\0å`9åDU_ªtΩ£ùπ—éqº^ƒÕ(œ≈’Òïãj!òÕ∆tXÆ'õäEÏ_ÿª•M∆êQd^b¡≥|ÄÚ,Ë{4\\MÚ∞X∞Ff˘-¨kN`7,¶˘‡BJG5¿&„*1LâÃ4	#£ñ-ÉÆ¸œ«`'\n£L?\0)≈|¿r	Xåòë|ùîÁe\nJ9@ ¨∂Ä»•¿6qƒX\"…qE¶	Pm—¬¢Nªà“ñ7§}	¯°<I\n™AçÕåj¢£u¯˜»L+Fˆ‹'î£CZ»d&RnõcI…≈lÚ\$ÑÄª\"â)|7À4hCvcs…≈}¬së™îG0~#f¿ËeêB∞•Ì.äír‡O!<]/çdÒÉë[A\$†©)öJÅPí±æ\0Y%êûF`&B˜¥ó¬vMïII†PÄ*7¿‰÷ê2á‘&l¸ûXoÄ.\0™KZîùBq&<J·p	îïeˇi;\rá°0ìêPB≈ŸHÖ“M≤ï¿Lò¸ƒ∞=…TﬁÚXÑöc1&y-I®6fNí|®§Ø&yR…n0r®	‰ó%V»¿Í RKRñd–ÄHû‡ Ä¥A†¸§Y\n‹Ë<Jƒ∫íÉìL±ú˙˘'Å~V \"ïúú•l!d Ë'Ä`îäqÂ¥˘´>Iit3:L…≤\\s%–Õ™°E@HC∂ò°Ïî\nf\"§áûÇ@ 1›1 l®nÕÜÄ˙™êºÓÁ/X\\âDK ‡^-≤nœ|¿\"Å\nâÉ8@ú{‡)P†Ê(P(‰Ús f y0Û¿Mò†@∞\0&b QX¶]3	ô8âÆñ<√Ê#11<Ã.b”ò‘√f*p'<Û4ö≈œ)1†\0Æ¿)ô⁄n¿~c»ÅT†Sò†ÊtIê11ö(\0ñP,ÄÇd\"=¸–@Ω6π≤\0Ç¿w\\’fzY†L÷n(õú›O}5	∫Õ‡W=Ñ“Ê›2YìÕñe@Ol‹Äπ7IªNÚìmX\0î˘ãN:nâ„ÔBòÚ\0¢k¿|®Ê,p>Nxnúxh¶Ì5ÈèŒò	¿Göd'Ä≈3ÈâMÿS\$H¿©1iâN‘0Û∏ú›Ä8πñMv”ƒù\0PÄ\\©–NHÛ\0|9Á¶@\0!dÄHßN…•Lî\nSÿû‘ÿÄöê*MQuû@&£7iã8Úìñú¿¶)1\0#LjrÛ3\\‡Á9HKŒﬁd”?ûhgü:	ÓOzvs‡î¯ß»…ÛO¢|\0F4ﬂÁ’>˘ˆœæpS’|ıÁ≥<*LBw)‰†<Ë®?9Ï–@	3Á•Ë+7äœ≤esÓúœ\0@ô”–Çyî\$ûÄ\n(#B–'–RÇ”´†≈Ë5Ci–†4:†ÁùC⁄ –æ}4D°î„(i<jÃPÙQõ¸”\0ADôÒœfÄ¥%°˝®á>πªLîı”4äTÄâ@ïI∞O·˙Xπê¿X ◊(î&lñ')}\$ÜeI±f∆N_% –4‡∆i≤\\¿±êUhC“=DßuìúÛ‡À'@ë‡v¢“8dB–-%(ìTü%“7¥Û„ñ®‘f\nÑX\0mèà@CŒ–0—ÚI¥±\r…ΩÄw<˙Q£ıhS0ë9@Ú⁄I,t¥')À¶\0J7Å∞\rÇóÀ\0û!Üá∆∑W1\0ÂÙ˘~∂_‘∆\r›2\nf‹äé˚ßú@QK–9\rÜõì\rXi{/π~™Çç£ß•›2Z_ÚôÙ—˙˘¬2'*oÅíú¨	Uÿ≥©Í\0°{”e(\$ß∏úi·M£4T4Ã4Ìß}6)‚∫ÑÀÙmV}AÍ3Q\0”Ïlî”/=@QZÖ:ÑkµNÉ¿¿|Q≠Ñ&¢’4J≥ÜªR*iSP‘®5Ñ‚á\nóÑÆt@ÊúÄ‘_ı)áïQIÄMXo™ﬁ†‰k19B7‡=–»‰«\0û…∑ÃÜlÄ|üÿ¶®[aa”.ß‘®ù∞\n\0Ω49ßŒív@Gö†¥ÄPO'åZH√X'VZ@T≤ïnõ‹Ÿgﬂ7>‚l3côDŸÊ”XZƒœfjáY´Ì_ÀmX) Ä¢zG¶°≈¿Ç‡\"P2|\0N‡jœXôÜÏÉâ{∫\0Á0d‰¢Tl¥û \nq;ŸﬂÅ:bSü°‰ hfy®’¯)äQ+jSCQ‡‰≤òyS…’∏ß0ÙHñq‡`	è⁄`“FπÑlÆpT+†yÁ“r∫jZ’K™c´°ÈπWmA÷:π«yË5ﬂ\0P&˙ö˙¬zW«…Zô)D¢	T˘vD´V∏’3V∫ı‘Fß»≠∞Rj÷≠˚®p≥vÆ5Ñ)öä—'X&@.∞ÂC@Á`õpT™∞lSw_™‚	¢¡#ﬂÌ:!/‘5¢rr‚–rø;∫Fª&´M@¿\\C\0\"ã\$ÿˇ(T€X+ä˛»\$t+”r¨ä84Xf∏÷IïíÏádÎ#&®ÄcIùPÎ”Z“ı”‰l¡Ã±(l¨¡Z˘É÷»Ã6^¶ËÇú3ïÊæ|Øïs≈\\‘=ÄÇE‡érÁ¡úäø3≠Ø©w+¨(±,å –cßƒ¿ã–^™|⁄:`Üh[€Uah˜t•§Z‘¿À‘∂ÀO;°ﬁqyàvÏ\\Í˘A^∞•ÑÒx!˝j2V’§’¥E¢™d¥0åÿ±ı÷∞4H´≤±∞YÅHz ï∂0+çÿøRjûÙ¥Ùâf_k¢µÖ•AJ¡jÃ‡[¥©,U\\jXçXÛ=¥©∞ZDw5uÀ§Ç”’ün˘	%'í£}ñ&æp&¥ )ùæ“¨q¥X‘÷\0+_9›C)ıI€ä)ÅR˝¨Ïßá`ƒµˇ¶@ÍÜ/!+UAf‚ˆñ·√\0R–=”AÛö¥%‡r3{î\0`%z0ÊÆ\$Í>—∏áï=¶hú¨]/˚6öÖñß4\0iï_2∂U∂´ïe™Ë¶;:J±NuÅV|Î@¯	®Ä¸G∫hUß=Qh'å(T>,˛nã?# ›ts•û˝fÁ©=c–Vvu`°U'X)÷M“È˜Q∫ùp’p7◊§!a¥òJ®lŸ0@ZFÁEï®=ClJd≠õû·Û”ÌuAJùt»™pòè0í¶WôºUwäÿÎ∆ÅÇÒ˜ëFa\niö›ªXÇ¢J*ª‡Ÿo*6äÜ⁄Ëk’8˝NÆ˜[*†/—uØMCUMaJîﬁ≤∂V!∂ΩÏUü!+€≈¨¥póxhÊ‡<@BÇÌ‚¿Ω] ;ÅÄÎ  ÌuîÆ≠–_2éRÒL∏≈Ã:«ﬂà	´4Ω.f1ÎÅ@b¨%\0«√‰!{¯=M€ø∞|ä¢`êxî	\n—Ço˙!p)_˝t„»æ˚å›˜#àépça•ø˝±i\\òêÔ3Dñ∏¿.à∂Ò∂ïÅY˜2¿x≈FÑg—ÎÑûπ8'(—0BJº…@b£Z£n	p\"Ee9Ñ†ªÇ¡JÁ0X3Ù´Ñb∏\r; ≈S¢1[y»=(73¿Ü	√ëÉë2úôî∏*¿»l0ÅÇ!V•lrâZ@<à¥ÇçüT∏ŸKmå·XiF\nUù⁄?fTà\$i8GS)L\$¨8B±iD!\\B#<4aTñ∑ª+à@Æ-¸7\\®–x6¬p∞°Éº?é\rî†N/Èª∞®%L+`∏h¿t¬Ã<Wá>·ï{¢Õ~(@Ï¸Ö¸·ÿR‰ô06«ûP+òæ{Es√∂\$Ò*Ÿºbã	ò&¶#•ÖÃ[XÃØòó¡˛&ÜùâÿﬁbÅ˘ÔnÇŒÒSÖÃUïØ∏lÊ,0G~Á}‡ÉcUf'dCs<m\r;ÿ<∆ÓÇ*4îŒ‹«¨«~±«âoam4∏]/Ó0ƒ‹2c»Fxw¶H;R‡‚ªqÔµæ&	kX„?AI∆†∆\">ÜÄ¥¨x–?∞˜,PƒÙb‰i≈´Ò)c<\\+Ÿ+†^n3í≈ë‘“‰|N'!+PGñN5ÏÅT∞Ÿ¡˛BKê†ëß!˘1\":¶2bP§,‰†Fy*“ˆN√ì<a[&¬3…¿≤tÈñá7û˘\$\\«qﬂî 2ecInﬁT„yï2öc_	@\nu˛p ¡¸xáˇ+çÁXûUq∑Å<ÆA.ÿ¬K ïˇ é!2•?ø8äfrÀó8ùÉ\r8(ÌÙp^±!ÎÄˆˇ◊!üY =q>Å¥\r‡v-œÄŸó∞Ø	À1‚∆g˛f,Ôı[„´,e'ZX:2\\H°Û†íÉ¯êy<Ä1)[Œ±“;‡èD|#©âH@¡äÅÚLS—3Ä®>;Ùí]2X¨vjÔ.GEﬂBi+dÆ%Éﬁ¬,Qr%–¶¬∂*˝üI‘Ë‡ã5`¶t—-…sñÅb™8EÕ€æêÄÉe\0=Á¥2Óø/Ëº˘Yq9-eZÆÑº1\\ Á“^ˆUΩÜùû`&g†WJéÀY◊hK]8W@;–pñÙ#†‚™Ë#Bäynqƒïúö\$uô‰¶YØÁó!·\$ßˆé)(rX@/+úL8…O^ã îp6,ºÂ⁄—∞w¬<%MS©S=Z%¥ÇWêËÃ\rÖ\nHy/¢2+eç⁄1¶E˝Ü…£\\ Uw	(p\n-∞√ÿI∂®SÓìEåÒZiI@1	òÙù•`„∆\$Ò44â•¥Ö8Ì”>\0¬Å‰i∑M˘å”à4ÊÉQê†j∫YΩ©y—p#Èx˙`”Óôì¶m'È•Z⁄Ç6ú®zaÈªS†i—&¥Ì® íR¸>zê\nˆûÙ√{TiˇP:ò‘ˆúój›ZjìT¡t¶RÔ˘®@:‡ã®ïﬁ≠5Å´hîj{\r‚fœ⁄rÒñΩ–\"áxú†|¶cxÕ?ßrÛ≤‡k˙®pÜë’.≤rÅ”>tq–C°™Í	k5hØ≠aÜé\nÛ≠çU:yÚ–Û•„ÆxW8Ëk∑‚◊)3⁄!“ãkÛ^‘t“}Ù“ñ-x5Ô^∏≤B(q@±◊Qd]∆¥Crÿ\"kw[&œ uÌsá÷W:…ÍïùNû@Ó¿◊”d±Ù∏âπ=∞ï≥+Z9©§NƒÙµ±∞ÑÅ≥@„æmÛœÎ{-%>ÁHÇ√¶∑áR0*î7K/<~úï·å,js÷“ˆnùßP\09.÷ÕµµÇ˛êSj\nÿÀ74é›±,Ì\$;E⁄Œ’ä-∂Ü«mÈ\0*»ªv‘¸7µc;u&v›÷≤¨37Ìÿ°ªy(∑àtıèn;Jﬂ€‡A∂ÔÅ⁄G4¢hf·Ò˘πRÜí@5Ä)V{[˛YÕ≈m‡b£≤©éË6˚∏1äëp€J›6„ä∏Õ¿Óú;[ê.–≈ä[rØñ‹b9§Vπ˜0≠ÀÓ¥\rw›Äì˜C∑óœw◊‡◊VT§†&=◊,‚h™ïzH‰Ä)ÍË†8ºùıEósIñt<@e+0y√È¨njÁT§§ﬁ∆ÆÄw©Ö~íd¡JˇÿœÉ˘´@˚)cã±+hÒ™,Ì˚Íäÿ´8pµÌL Kœ√:QÓA≠Òèëogôı◊1ƒoüÅÁª?I Z.∆?¡=~îﬂÅ¨•nπ∞º©kF¨!n%/ÈEüt0'ÃîÄP<∆µÖG¬qP‰¥ìF¶ŒxA¯qøµÍƒ◊‚É´vnã`,˘∫cW¿{·9K˙ùﬂá{|±+s£<È£˜4Z+◊¶π6¡PÖÈPLø»Ÿ«¿ù(L=º’Æ°ójfæhã€>)ΩAÔÌò†˝qñˇpKÃÜºòê’Â“†¸~¿6d0Ä•‘YΩ#yø}¸tO∆Ó∞R˝ÊCS∆_≤Ááúﬂ»|bHwÎØsÖO%U–w‚pŸ‹NÚàçú§â∫Y]Èï∆Ìﬁ”U\"rMÓt¶˘ª\0jxoW¶D¡ÉÀ[[ÃM± ÿy∑ƒT¿Úï8¸√@∑9ò‡háì÷‚ò!ö˘üèÃãr`õÔ‡ã\\/Æ4¡u{úd÷8S«°¡sbπ\"Ú §¡Å¨iÈ;ô˙ji®«ø¨k˝j}v£i÷74ﬂΩ≠J√‰9=’ó540'˘?õÌ’(ﬁ7ˆéqg˚¯‡ t	Ùä_∂‚›¸[ß˙ûÌzÒ”å\\wÃ_>s«¡ì_ˇêﬁ“g\0πÁ∑˙©åVú|\$‰pú∏-ΩﬁBsX‹á¿.«Ÿ»;ˇæ3êÑó§g≤˚ÄPCDπÄÍGy1âûÇj\0y=MÀû;Fü–m(¬oD7y≥k«˜¡ÃbÂoî=Á!:í.Å”%Cù%Ì∏tﬂøëπ≤Xm\$ΩÃ6&ˆP…bjÅ¿ÎTﬁu—*¿TxÄ\n¿d5æºıÏŒùt^d≥(S|≤Ù◊-qÀä¯„—ëÔ´\0©®≈˙(tXYQ!HùFÓ¥ká≥˜‡∑0t´òûíÊ4H|ä≥oNo˚»Nïî%∞\\Ü«w\"0ΩŒBqãµ\$[Áéô˘¬fé|qõçŒ¸7~Ey÷ÌÓ•X∫°Áóq¯◊®>|Î Ob*—\n“≈ËImﬂcÀE–ÆÙ∫e»–6eü¶¸vÀüL¿…’n…©‰Kxx~a˙õ«ú¿f)9ÑÀü]F¶!§sÚIâiNƒh~·Å”î©É◊R£˙“ﬁ.Ï˜µúêÏØGFùΩ˙˜´å8¢Ô/ÜzdCïf6-ì#g|˚ŒÔΩt€¬ä–;øﬁ÷4ôTVÙ)∑kVﬁﬂÅ”Ò/yÑ¿C†◊¿É–…9Ú 07h@úÎ‹Ú).Hq„ùÄE›ÓÒN}¸öKØ+õÿYÄrπ\nb3@åÿK1 ÷)ÜlûAÀßﬁ=#ú´HiLÆ˝Õ Ñ5ãoæAÄÜÔ„ÕìóB>Yã@\n1H∫‡∑!+‚◊»£sº0ËGH~^7¿ŸÄ†–√…QrIÙ8≤Õ\0√å–`§á\nw¶=0Aõy®[Q⁄8H „¢O°ò¸g mºñÔ# ÆukHBßˇé∞#∞oõuf†o›êÍk†Ì„éÒ^!ˇÒp{¿}ªöõÿΩ4IvΩó∫Ì≈˚?x{®õ‰CY¨-ÂIC◊û–ıÛª»í>0§˚l\r•—\0∞ÿ|Q◊1Âœ5Lˆ/±ˆÓjø˘3;õLÔ¥∑ç^Ô{∆Uﬁn(}Ì∫ˇÓÃbΩÕWë»Ÿ°‹‰+ö>ÊÔ'∏∑â—{WsC~qM;P‰ÈRøvÃ¢◊∆ä∫:p˙‡ÛQöÔ’G¿‡ 7ÑÑaß;¿È·è_œzã‹Ê)|ø£¡:g\0Yá*∆/kƒó\nó >UÚ¿0êxüH@Î-=\"0H^U∞òE+“x+ˇ˚#∆;Ë·™1ÅØk≈y˙í¥É£ÕTh¸:G€&™-å!qsá3^|˙€‡xWÎ-lÎÉ˝!◊∏çÌF∞˜XìÙt]«ÓBXY;QÄLŒâ ãΩÍÈ0cIƒojËÈÑA¯Q∫˝ä∆‡L˛˘GG„‚à%\$(w“π–Eh»XKaπ∑ÔÁ—o˙∫bèÄÅø5ÀŒ¯˙ƒêÖãsA’‚êt/\rá›í`≠w’7<MP¥ñ*yYøh>PÓÇráÃ=zjW01ˇg˘dl˛iD/‚}^V…\"b∑¿>–îõü·‡XºÄêRnõœ›‚Érà.0ıÎ¸ˇÙÃô9@ÃŸ–Ê •‹ˇ€Æ»∑÷;Â&≥^˚2öÇhYXh£(¥ˇ°b†á\0¶ÿÄ¡/‹\0 l∆:0‰˜‹Ç≈?àÛ√t%•> ¿ÄêCG4@÷Ì≠@ÀEØ<êì„ ¿h	OåÍ0K‰\0á@r‡[Ï\"±æ¿)õAŒoXÅ4ßzº ËπNR∫÷ÃÉ´`ˆºj‰Äk¨»¿¶PéÅáî £]Oàl¿Ç˜Î2\nÏ≥Ô*ªbΩ5Dn€ˆÄÔÖí„2çÚà(˛\$¡”<)ªHac:∂œãÉ/À8¿i:˘n6:‡0;Œ<1˙LP\$ ÿ£‚Y¬˛ã\$ª°≥Æ—:0õ¥¢®µñÉáÇéjIÅPæ\nìrL!wîå¢˚íäN\0ù>~/`4…+\0Ê§¡<âÄ^RX∞UÜ6¶Ñ…:\0ˆñbN¬Ëéå*Ä.ÈNØÃpxp_∂√ 8\0Xo¬KbËòñç|…l\0∆ñ‹¬ˆ)\0í∞PÄ¡™:<pl•\nî@ªAΩSPP∞∫¨∆öê\\ª“ A‘◊03\0006 ∫¯(‡.«ÿ”pv¥}ÿÖ9©z´˝‰µ¶¿@N\$≈Ü?5ß„Öüºi+Avì8`ªÄy®ü ®Ç¶\n;‘ π ÛÍVÄÖ§pÅﬂÄ˙\"œÔj§ÌïE=Ÿx¡0d\$ßPË–VÖ	xﬂXçÒ†Îg\\?\0ePaAJ/`”pS§¡L–ô¡	®(PYBq¡Œ–ûAÔ!.êb¬Vsê™°\$ﬂ	|Pf%gzT„£A•§–k¡Ω‘0l%.å¢l•5I¶…+ì8I+∂ìíìêëBH∏*©p¬Q\nëéG^B´	rLPUBµq†êhBºñ»ÈnB¬îú,bË§Ÿ4Ä∫¡ÕY◊`ùÖ	|#`.BÊí‘.≠ŒBÔÃ\$üÜ6!Ï*–s\$‚î#<àB%°ÄêãÄQÃ*Ûîe	âNê“¬àïrL–⁄\0ŒN1!i+\0∑„—§˜\"å60bCgaNêﬁ\rPUCq‰/P—\$BNIB¡µ,%#£-˜\r¥+eò≥h&p¬¬∫/d+ƒ¡∑P≤C-…B’D;DÖC}<BBìî:0®√è∏\rPÓCPË–ÙBiÃ1¿ï¡rî£Œ	‡'êéÇ¿Ïc[¡É\r?Pï*?–Ê¬≥¸+pÒA∑,1Q\0√L@∞qDh.–˜ΩYàP˘¬√®ê˚§¥`0¢@áÁ§6Q∑b\n\rÄﬁï¨0ë√\r\$1¡≠@ê≤√ÿú=ƒ∫B÷î®ê -ÄtÍX∞ÏB˘îC1Æì,©+BIîêÏœ¥%Ä˘†	‡‡÷≥(Ÿ–ÌKT\0ﬁ–F@¨/¢7X·\nD“ÿ`Äàú`„[ŒïÒ∂pîD«¨L«˙D–ºQ\0\0ÓúN`3Ä^†\n@Ä∞%»	9¡¸ß¯ùÄÑõ\0ÿÖÛÄ[ ˛	≥L›ƒœ‘MAÎØ§Qì2Q8)î˙HWÒGDÌbÄ%\npã	ÿSö™†ÖÑÄ(‡#∂tñ¡ëã⁄DÚíHQq[…fë]É\\'(BÅ@Ä^·(CCvƒ˙åV±[≈ò`(Ò^E°¸Zc!Ñ7∫√ëEÃ*åY1mEuÏYÒ_E•§\\`ˇE c,[1e≈◊Ù]—`E·∞\"‰Ü¨86 ◊Òz≈qº]±h≈ÄxÌ@ÜÖÃÁOEÆô\$Oê6•}¿Qq=Äõ´ô!\n≈ê<b—:µQ‹c¢˘OäÜ∆'ÑbÔ“\n‹T‡≈(|QqF≈åR`&E*1‘R„!L^—f≈ŸÏ`—`≈ÜÒc\0^HÅ!ëì∆yg—|FÉ\rÒbFä¶Älë<∆êÃcqeFô‰i—pF°îhœñ\0ÀÃ]Q¶∆Ùhqb∆®4O #\$=\$g±ÆFœîkÒ¢Fåp.™<\0ÂÑk`©∆¸në¥∆¶ß‰O(J ≥‰[qøFªj1ªF÷4§\\(º§í3\\TgˇD-úT—CA¥+ \r‡7ÄŒÌMÄÄ‡xæ¡	\0Z	Rõ\0005ÜÖp\r1’E\nÏVèI∆(;RÖö~[>`3Ñ6ò§rpîô	‘%†¿⁄-∞¡–ñ\0˘	dC√±¸(9¶—¯∆AÇxì@2¡˛§!Å±›*`\0002«≤~8SÒÂå„	P°ïA⁄èä/ ∆#Êè©â-ß8Ä1Ä§n¡⁄tÄ*\0Ü#O±¯É0=0	Ä'\0d†Ä	¿í( x‡G˘  \$É”\0†HÙ‡(¶2ä\n≤¶3¯ÄfÔÓn‚ 7‚\nÃÉ`7G˛F@>H.·5 >?ˇËB>«<„zÚ\$`°¿>0–R®˙tY¥Ö„∂∞+ ¬FÆ‡QŒÉÉ ‰l@>\0ä”ÃÖ1∑0î´\$VÚ∆¥KÚ&\0Ω§ä@0µ ˚H‡>4˙‰o\0006∆ÙüqÓ∂8ª1√P=9∆\nÆê1∞7Í≤\0¶ùD*“<ÄÒí#Hë\"1|Ú#£È!K3“=~=nmÚHÓò,ÄÒ#ÙÖ,{I#⁄tÑ√…~)á ÷ì\r b6ÚI!1g‡E¢/ÏírÅ\$‰í‰≈)\0ÙÑÖÛÄ¯»“6îÃé'ﬂ0v3g@Ü…\\˙@\0¬è!π â3!4è‰©HdL˘f√_Ë∞9rå,[åxÎeb–f ˇHHhpf1~»D%,Ñ¡a)î0†÷•„%<ÉN¥©¯cÄ>Ω& K!K0¬-ÿÄ;¯ÕH0™¢Ç&ó‹è‡¨õ.¡|#¥á∞.»@3…·!KÂ\0002\0Û!â07â øÉeJ9hø≤\\=dTúÚ\\\0ﬂQôDI´d∞\r(II 8âDrÑZ~(;b›J8À¿Cå∂U!£RHº\ràª 3IÙ¶ƒó°_ù„˝A…N#ì32ô®Ê1¯@‰˙{¨£@ˇJ+#®=R}à˙2‡-â~H. !`:Ä‡…TóÒ^Ç!K˚†)ÑJ–ª(\\Ö-\"#í• L0	ít É)dÖ/ÔÑõ!àrãî]8∏Ñ—ãÙ.SK“ê\0åH\\wG‡:√)ÑG˜∑ òºÆØ€5+»N	ãîÊN‡\rå JÃÀ“ˇÚæt°0&Ç(\n·aãÕ!IÑs·!!dÄ1º0Ç≤ DË	2:À5®¨·â3)∞WÓµJ%ãΩÚ¡:0‚≈“ÀÇ≤#Z*üHÎ-kÁ◊¥Ê\r»R≤⁄<-|Zí÷2ßLµ¿÷\0¨¯dxÊõKäAty2vãÆL≠A8 \0…K≤≤3ñ+\\¨íäÉºˇ{eÔˆÉºÎº††àÖ‰Ï!÷≤Ôºˇ\$¨g„I™.√¶…O#´ ÚùÇµ)ÿ>∞\$g!PÿÄ6KFË#“q\n÷WÿP2NàzS{)|à/'ø≤˚±Î(kÑÚ·•¯fQ*“ÏJdJ¥ªÄ>≠©¯¡û!0ì2Ú\0—0ìRbr ⁄4úï¡yJò™†\r <ÿ^êH@ÇÃ∏Sô©∞d¡¡WlT≈ÛÃ\\¶Tº£Ë¶T√F;q!‡MC»x!\$∫Å÷9≤_ÒAy?˘ñ≤z∏2|ΩI|ÑÀ.´Ï2≤)*c¥BRÄã+‹¿ÇÀ2‘≠`Kõ2˚\\RÂÀõ+¿BÛ0ÀôL©ì4ÃÕ3d¿†)Ã»ﬂ(≠ÀG,¯Ñ\"¥ÃàÙØ“í3xøS?L’3<ŒSAM.tœ“˘2≥.ã¡ !+∑%8!¡JîHêé@àµK%˚±Ã Õ4√ˇ≥AÃŸ3ì±≥NÕ*d‘, M4‰/≈ø£Zc¶ÕO1∏\râ\0\0ﬂ(§¢¡ßM</‘÷”QÃÂ5Ã≠38J75‰ÃS_=ç5‹©Aä-è0í:)~HR‡Qt¨Õë\0\rsdGsdL∫.ÃÇÛ¥[6@÷‚5óÈ6»2•MêÃí†-/Õ∆	\0cÛLÕŒ¶˘Ã¿‰M»˚ˇ≥eÄ›7x≥që\0003ábΩ4å—Ÿ4`1.j‰≠@ïH46\$W‡6\0Ñ\r™ô≤iŒ\n©Ù∫°y\0±8h;…Ì>Ã—€Q_<40?RH10€G†(L¸fQ5/∞ÑxXíÜ¯›så≠§bLß`<âÀ8{£rß¥˚SÏ·ë…≠9@BÁﬂΩ8x6‡Ø'¥àN\nûŒRŸQf`¸Ä›8|Ë°\n'¥ÿ‘üÔª6T(2^A*/ù'0E≤t *ºx·…á#dÀg‡Hÿ“Î≤RHŸ(BõÛ∞∂U#`\n†\$ŒÃc±‚\0(#È9Ïß2\r>§ÛÍã‹\nÃ`Cr˝KÚHP ©£Ï¡€eP7à≠\$8“Ü…6ÑΩÒËY∞6®|∂Tªÿh‡¬ú∫†R–Ñ,4s≥«ä*¨à ËKú™)DsÄJVŸ‘üÇı5¶\nôMH?Ì/|ı\0006@.L¿h˘À®¿>Û∆KvßLô\"!K(\nìÀI¥£§˜°Ov§Û)I óå¯ìÃœv6√ú3„®È>dÚèÄ•!å˜‡áO¶∏f/ﬁK∫–é•ŒLvûÄÖ0¬	Tí\nÿë,·,À«„»‹π–6»M≥|í∫Odª!'U=√C/¯Jd*¨«≠ùF<ˇ”∏•±!H;†⁄Ç;—ôA~ó^<aSe∏2÷ò°=Oı1ï 9L{9Ë†,:≠9–†,–7ô¯µ∏-\0`∂\0÷`∏ Ãu:1| Äe1ÃV·L#Aå∆	œAp\rì™˙`¿©Ã7)å∆@‡ô,dƒ°Î–{+ØÛ‹ «5Ö\0bÏÕ`á∞§Lh¶U\n•–¶e\nSÔŒñ}‰á\0÷–JAÒ¯!2\0ÁA,u\0006PKQ|3O	PSÄ68\0%@0…¶\nòL≠Ú5.…ô Ä§\0',î‘<Ä‰)@¢ìÌÜ?(P5ÛJä®«gçI™\rdóÄ9\0Ó˘TÃí¥É)–BÚLA\$cªb)¸¶\rò»XˇËC¥O≥(Ï\$¬N—J†ÑqHV `\"Mï8ætÒÏ∆\rã;0ª3|QC7≈ìXLu1}3X:¸bO4]©îÎUíÁMaE»?T=ÄÂ%0§‚êÇC!√TÆ¬LéH¯«`’—*PÅ8KÇ/\\ŸÀK6{ÔSY+Äì∑Õê™¥è‡‰KQ6@6ÙVÕë<ÉeIÄPØ6CS‘zOŸ\0’ÀPåf±õÄ·»>°òû80‡¬«Ù»aœ\"gI,gmF˚¢Á·ôîr†ÆIdä|ÉFÑR\$ç ·»ãHÒ!ì2F‹¶P ÜHR∏	Óã≤∏	±8Ñ≤a»5îïÉıIh.ÅQL…(@;”\r≥ZÅ—3%\0=)@ÿB‡*«ÌH¯?T£\0Ü#üÙ¢«ÌI\rîí–JpıÙïL…JU%™eR_IÂ&@:R±%’&‘™Rq+*ã¯R∏Y∞?R\"ÜùJÖ*R®3ÀJ∞i‘¨Ç\"˝,Ù∏“¡F5&j‰Ã6Ïç.¢àä'KE+‘£À&u)T∂RML)Tî∞£LD~‘πR∂Eî¬SKΩ	‘§”K’1Ä!“ÕLÑ™†7∆≈D¿cëKÍ-3Ú4”D˜Çâ”A/x%T@SV¡I±˚S^= ¿ê`\ríÀÍ\n5#î€0	R2QÇ8™ÚTñI#®%S∑¬w∫¥ﬁÇê|êRœ7”ú¡/E” \0è∏ø¥ÎÖõá~0A/Ç\$≈⁄”ø@82L!döÉ¶“–òtçY∞OÙ|7<aú(º Æ6≠4d@\rãO∏;t‘úA≈?ÖQ∂iI·èêæ|&Ä,í<w—Òéçòé¿-¶‰¢ì¡C¬éì§ÒÇ\$0€ÄÎ+à¨ÂÇB1îÙ≠ìPÒ\"äû†¨“*¶Ë#\0„H˛ß≠E£JO ˙¨í5∆\0\rå¢Ò]O—Ï≤à≤^\rxzÚ:É^	Ú¡_(¥ó¡6M'%[…-iá]F‡#“ìÄÂN¸øÀ˜\0ƒï¡LN≈4≤f’#i:SàX¬@4Æ˜%˘[¥ ôï Õ=„á&#eí™ Hcı\0¬ÄÄ8¯f  ∆pè4É\0ˆagÆ‡Ã/eD,A⁄Ä@‡bE	\$PıƒJj‘2\0É*î:—√ .âè√Á24‡£®-P@uì»=Ù†)“\n=x )\0ÇòêÚîÇÄ†=Ë\n`+ß√H()\0#Äòx˜Ä&†=:ki”&ºÄ)ëH+>ùUÈﬂ’áChi\0ÄOZ~@+ì¬x	Ä\"¿.F?Bù	ÙBµmAéî¸C…[¢ÌDË’mQuFájîµR/4¡√T§uK%âT¬ìMDñcÚW!B\0ÑE…¡–ìê`…è=Ë™“&L=ÙÄcœèB(\nÃ®[€–0‚U\n …’O∏œå1ïEUTu]í\rU›\\DÈ[Üù∏\rÄûGÀÌ`1÷'XµdÄÑU¡TÊ?C£ºHµsV3WEcuu’!XÎuê\$Á‡â,B‚}e5~ƒUµPƒ1÷.ñê'ïïA:>pæ Ç√®ïçi—\n§˘˙Së›Sµ•E\n1–\"±T@µ@#¡≥lN‡1ê[(√±:≥¡§PQÀUÕl4D’[%l—–Ñ%[Xu∑D˙5∫bç¢	 kHÅb◊DµpqìW]p@9\0[R8°ı≈ä⁄(-R«N©Ò.£¬.UpçTW%\\·*UÃ¶)\\·ÉÅ@Ä…C≠tUÕüﬁj\"\0<WRc}uu≈u]v3íÇºQïsı’◊!]}ßàWX]≠wì∑◊.¶muÍ<I\\ 5ï◊πÜáÕr’Ÿäò†dÊh◊^usMÛÜû∏+ 9œEu‡É\nj……WºΩyí„^Ë∆UÚW[_E{ı“Ñ„H–>µˆW»Aı~U·ÉÔ_®:†ËÆk]xŸµ˛\0b(‰\\B◊\$ıÄ89ç\\êd‡ÒX4çÄUŸÖaËH8WÏ#esÔúÖ^ÈÄA?¯+=uÛˇ\0◊`ôƒµÍï ’|\0‰z)˝uÔûpµ””„QΩu‡‚)î`£îìÈ]xOu@6æew‡ÿ`qç‰≈»9á∆?ΩÑ ≈W@hÒç v#	À@mâ\$©Xõò+ <◊~(„f\"éjßD¢∂◊eã¿1X¡_=rA=øõW9ó\\ 5ÿÀc=åE@»lÄ†1X¥Ãê: ±^È∆\0¿\$=bMâs˚ÿÆò¢FVØùbä\$©Y	b8WˆCÖT›s6%Y ïí@Ÿ(-í÷)ŸT⁄bçù±/≠äDÖÿëd≠ê∂HÿúÆL«`¿@d	VGŸ=eUã¸Ä–—˘¯vX\neïî÷ZWF{≠óMMY9eMìç—≤uç†2GN(ùò-(Ÿ+bµò÷	K_fX#÷fª9dÂä÷cÿvnRbâä%4'-ó\"›\n9\$(Jï◊W%f’ô6j@7gvr4ˇd/1”…œgê’◊Ä€[eåÖ}”ác%ò‡Ü…œc®‚NÑJ\$È¯aaŸ¨NÖõ	Y2îdS∏⁄b≠°Qççõ`\0≈†ØW \rÖû¬r⁄,H¸Æ\rÜÈh≈rQ˘ÖÊ\"∞Ç‡∫ái\nïÃF⁄KKò;ˆî∑iP+V™ôiu§p⁄c3]÷öZ`8j\0ÿ-âπï◊⁄Eiÿ•—ÿ+d]ä\"ŸâhÉ	VLœº¶≈ï’ÔŸf}¢1ŸŸjù°6¶∆7dMâˆ\"XYj¿W@Ÿ°ù†A\rÖe%¨\0¢⁄≈j™bñtYß:Ö°ˆ™Zµò€·ß⁄“e≠vπ¶(’Æ6¨äd»A°Z˚k\r¨iäE¢ßKú6¡⁄’l*—<J]huÆˆº⁄ˇ⁄ ®Æ®¢ñ6[#lbÚ;ÿÛb’≥v∂dtt‡7ÿJt∞AßÇºŸà∏Å8ÿÚ–ﬁA\nlx“ñπZ˛&∞!~©“ò•¥Äï[OmH∂’¥~µ <\0Èmç´ˆ⁄Zômª‰,◊[Gd∂uÈåi-üDqZLºÓ66[çnE∏ è€áeÃj1¬[l•ì6ﬁóJ}sÇ/[ªme∏ñÕ#Ëmé∆⁄J§πvÚF7n?JtÄ[RÛ\\6–Yoä÷LÖ±c`‡5⁄Ìo˝Ø÷ÚÎ¸vmñXÁ-˝å6µâa†cÙˆZsl%°C@£[âïïäWa¨ ‘÷Ì[ûıºÇW[Moï∂≈g\\±¨/\0≈oÿ \"µÉsbMù†9É´dÕƒ£◊¿ó„‰5«8<v÷#4“m«,}ŸÜÁßV|Ml•üÇS\\ŒˆÇÖim;ˇÁ·Üûª€‚+YQrE»∑%⁄…rj∆7€Øa}°¿®Z˙MrW\"‹ùs 3ˇÿä(ÍBó4‹¡q‡76qæCoÖ*µŸ+q≠∆Òç‹€hsûv<ÖÖo’îW-€…rÌì7?‹ßt\r∑ Äÿµt-ìó>tH·)≠rJ7\"@w]≈¨7IYãpm“∑7XYt\nG¨ ]\rp\r—Q\n8\n√2Ä—∞TezP›\0Ó=’v›NE…#¡‹ΩuçÃóE›iu-ÇóN÷ÀsÌ‘^›.È}»˜C‹ïdÀÇ2¡]1fΩ¨.g›r0È4÷¿⁄x∞ıë>WvE⁄7.]ój›€U«\\k4çqˆ!‹ıtçÿwI›¡F]pwqÑ5w)\0ï˝›—qµ“p\0\"\0_az]^€7v]≈[-‹ó<›ÂvﬁñL7Æc]Éa7?\$Ø-‹7;^w5‡vµ]=va°g⁄vÌºÄØ	k\$w± ‹;ZÄ!µ©VÈql@;V0Ìl˜\\ã[Ùv-Û›˝`X ∑xWı_Ω“\"¯‹n ‡<^uwÌŸƒ^ew=”∑õ]’ƒrâuπpÒè·ê*≤uÅr–Z[]ô˛¡úGƒ>√©5C«ÃªÄÚ…(0*…íÄåòµV0WÇ≈	†ë†¶†(\n’Ç®[z»Ú`)^∏Zk…±'‰#≈Ó\n\n^‰()ó∑ÄíöBiWº  \n∞\nÉ›¶ àÚ7∂ﬁﬁ<àÈ•'–@	†∞^∆:¿3\0*´@∫ÌÏ\n–èJê„”&h\n¿ÄÖ|Ô Ã°zıÏ\0)_\n=Ï††_D=h\n5_Zòåí`*\0≤∫»3“.¿∫(\n¿'¶Œê„!h'º(ı`¬´><à -\0ã{zi /ÄÆL8Ú`/¶:¯IãUvù@Z]ÄÜ<çÏ◊¿_{¿0£À÷~=ı`ﬂm{,7‘E{=Ì+£c~Úló∑ﬂ«}5ÏWÛ_≤X◊…&\$Jk◊ıè1m¸7˜ﬂO{%¸‡´8hx`\"¶¿·≠˚∑˝è'{}Ó7∞^Ë¢çÓ˜–ÄtÖÚ@\$Ä¢ö’Óó˙Äôz–\nÖ†ùViﬂﬂ±{¯iã&J≈ˇWÔﬁﬂ|ÿ\n	•UZÕ˛wœ_ÄNW˛ﬂ(ªˇÈ:_RÑRΩﬂ3}Êó˛`&\n≤iAh0ô∆\0∏	°|EÒSﬁﬂå‡§_%Ú√ _2÷†ˆ7Œﬂ∂8Õ˝ÿﬂ…|{8%_Y}pcﬂ\0á}çˆk¶_kÇÚ©ã_s}›˜†´_~Œè?Ä`	◊ª‡{µÏ˘‡Lôpf8;\0°Å^8\0•ÅÜò_∏E`†\"b<˙wÈÛﬂç~@	˜Â_ò-˘ÿJﬂ£ÉÃÄó∫_≠Åò-ÉÇ:fW∑_√~M˘iÙa1~~wÈ·=Ä&Ì_?ÑZo˜∑’uU‡Ú7∫|ùXJ≥aUrbıdﬂpZ	ﬁ’®<÷wÍﬁ–ô0f#‘U{~@&_Ê·Ä	Ä*Äöòæ£‘ÄØ{zwÿ_3ù`†#ﬁ¿\$(0∏gU•ÉPX?ŒáRk`>+\$.8gﬂ√Üç˛x‡Ö‚lIﬂa„Ü›¸8[’{Ö’X8aﬂÊùÛI“·˜ÜÄ\n∏\r‡c~ﬁÄaÕÖµWx‡?à\rˇ8&∞L>!ó¬^µÄ|˜⁄èt=Âa◊`=îáÿçﬂ´ %ÎÈà††‚d	‘‚M}x\nW¥è+|-Ï ·ÅU„”x	iêaCÖòÚ’WèaÜ-ÚsáﬂØÄÿÚ…Ó'f(ZŸ'}â™Ë„◊¶\"^'Èàbìäò\nxkb™=5˝É…ﬂh“dC›èz=6\$CÿÄÇ&+∏¶≠~û%8µ\0Wâ`Ù¥‚'âàÚÉÀ`ãv&ıY‡ãµ˚¯\n&0<‚ÄcÕ®‘<∏	 !MÿÛÉœ\0°ä˛,8êb…äñ\$òƒè?EÌX…c\nµ˛™›Uî≠&AhaÌÄ10ÿ√„â8¯∂‚VªF2ÿπ‚eçNIàbqãﬁ3∑¿>¯÷\0Vù..∏ÿÄWÉúà…ó¶bò∞Ä#a™=BÏÍœ®[äPâà*=Ä	É–*‡ò Ë∏Ç`7åˆ∏q+H∫ 	`\$‚ëUÿ5k^∫=–Z	‰bö=ÌÌ≤„ª}˜XÛcΩÅ.(∏ı°´Üd√_3è)¯é_ß3êÙC“b‹\nF1∏±èOåÜ,ıÖ’Qã≈˚ycıãˆ.∏#¶cãÿÛ	ø„Èè.A©º`>ôçÌ5ÖßLöF9¶:<∆+uÖ‡ﬂã\"â™‰ÄVï[(=>X%“å=H\nS9aÃÜx„IëvE ´a[ &2ã£dh=PZVU]UÜ#äƒd^\n∂âò„7ëFGô\"&ΩÅËT£&ïëﬁ‡‚ÂáF?∏ê‰\0&Hy)bÔêÓK9)„w;P\n˘\$d£èÊ#¯ˇbÀâ&J≥ßgÄvK# =ënLX”„çìH*ÿß^€Ä˝ *'KìéïÜ·âçnπ,¶`Æ:Ÿ:\0V·†	)Ô&!|ŒPÿ≤¶¡éVOÇÂê¶-√‘ß}îö{∏∑b	ì÷PÿEe#îÊOòõcgÅ˛P¿+’Zrt˘(O{ÿÙrÆ‚òŒByLèNòÓ2¯2Â(ù&U	ãﬂéû\0XÍ„ÅÄ™Ïk§èMç¿f5]÷òÜVXUU\\<⁄pòÌaH“#\0ò=ˆZò‡èXvW	ﬂ·§,R˚`Öäÿ)òfﬂïÜ6Uc·Ñù0ÿÎ,úbÖÉ–’®f'`%·õîŒ©ée›Äﬁ^8\0üÄ˛^â˙ß“.\nX__≠ä¶P\0)¡Èá.B@´dëva9)'πë˛N˜◊Â˛ùO˜Ò+~æ+¿´+Ä¶XAò¶-ëÄ	ıj3ò®	ÆèMò˛aXÇ’]É\"f)ŸÄ™>y:ÊL‡\n˜≈Ö°Ä∏Úò4ÄVôΩX¯Å_ÖípÈ‡0=-˝@¬ßz™ÌUYÖbñÿ√\0®∏µÇ»∏x\nô&ÒöÜjX‡&ìö®*…ã_r{R7èKà÷iâó+q3ñkw≥Ä±õ8f9öœ{Ç6]Èêaö†Í¥§ÄVõl˘bÄíõ>jyÆfÔõﬁy¥Ç¨<≈Û)ó÷ú(\nkfÒújâ‘´@≠X \0Q~∏˜ÿ@ÂéF8Cﬂ÷L0Ù@*\0æõ`ÚŸŒ^’~rÖxÆgD∫6tïTﬂ|‰Äx£·# >uŸ“‚	ùe)Yœ™«ùÆuÒ˙ÄáùJky’Áö2Ë√ &±öeU¯{ßMä;™–‚iâûU∑‚èL=Íu#◊g£X*t…ﬂ‰+å0◊æ»Åö†	 'µú•˙ÓUYä^)5V’ìãÆ*Xû-U›a\"™«Ü¶ “8`≠}aÂ◊ï’UŸ{gÊ≠Œ˘‚d°n-8ª\0üÖÊt9¶î<ÊLµÑd´áh˜ ,Äæ=I0⁄hü8Ú˙¶\"öÚ¥:Äæü8	‘£cÅÿ3h1å\0iòßßz8Ñ÷ô6Ñcûíd∑∂Ë8≠¡1Œhiå÷Ü…ﬂ'ÅÄıXÀ9ô|´£Ãè_ñïaÇËK†≤¥)≠ﬂØXnâ9µ®Z0Ú5Ñ\0åõ∆àx·\0û<–ò‡„∏†‡Ëû»\nXΩË¿.Uò‡¶Wéic…‡:ª:É´¥ß∫û¯	ΩÄê˛Q&ßµ†Ä\nZ\rì<ˆÑY«ËÒ£÷É£ÕËˇ£Í¥9VgµáRË∏∂Æøã™b ®Z´Çb˘˙‰*òÂIéG˛ò‡k¢Ä©¢ cö.™ΩármP\0≥3ñY\0\"'æü¬Ö™2ﬁÔUjÖI˘iI¢∫¥9Å»†]W`+ËËL>é“-c»<†¯|\0øâñZÁÖ•Ê\0∏Ëe∂{©ÛÄâéNåã£H¥ò∂É⁄=h+§&ô˙@·1¶¶d∫S^øáhÙy=fWöûÿUa3~Ö˙XNË;äBãâº+FüO⁄va\"M¯⁄wgÌÑÜc»Äòû“ÌöG'¡†Ú∫Gi·¢b)ﬁÁÌî@ˆXC∏f<ˆ\0∏»èêÙÿ``Ø⁄¥:àc<»öpè6<∆äc—aÔX^YÑcÚ=-axÄíûûCRK®Z<∆õ “cf%æ?¯ëÄÉ©n,ŸÜgÁ®ÆÇö>h/úx˜⁄HÁ≠ëVyeËâ∆YWVZÄß}lÅ©à\0±Ä-Ûπ˚jèñûO8≤_[ï0\nY\\ßQ™~úÄ#ÄøênÖZ§Äøä>wyÏ‡ß™ÿıY_[•ËˆX\rÍ∂öﬁ´∏|egUj∑:´’ç£ñsÀ¢aÇ¿\nZ&»≠\r`ä“.è´ùÓI‘`{ç¶.πnÂÜ≠´¯\nÊqï∆ñW…d.üí¥)“ÊañÆ`>gÜö‘ŒXã‰}ü8Ú∏ãÄ≤ª(ˆ\n“.Õõv{∏Tè7úàÄ,è3£Œd´£gª~0Ûÿî·§Ù	<ê{Œ<}∞3ßÇ\$Õœî)±8à@ÜR34®:Ìï≠⁄‰µ•]º‡~6U!<ñ@ÌjZù ÷∫SÑãÿ^¿é”à¥úBÄ~ 5Ù„…≥,ùõS®,(òMÛ©Î’9p 3ÏŒX©/ZÊ€u'¯:∑Fœp\\Ë)|∆£ØX\r¿<áY8XtËÑï!`6 4∆Û∞.∏‡6ÏŸ–+ZˇP€∞Än°Ò…<≠0ò€Ò&êbîwÆ0>¡äSr<ß@6´d\nïÉz„lO∞˙FRlO±;H5∞⁄ù*Ñ∫L^x0∂>g6ñs†/„qúëYe\0àã@<ßz=\$ãCÿ¶ïû¯\n`+'S£é9Î‰Xı8´_/ÇæäiÒjˆà	∑Ãh7≤5Y\0&Üc©Íw¯Õ‰aô÷°ÿEÏ…ü≠XyfuáŒb{5ÏÕ≥vGµjg„ànŒXdÏË\n≤{É‘l«´ÜÕôäß≥~≠ÏVÖ°≥Ë	?¶3¥Ã˜∑ÈW}Êä«R	Ch0Ê>RΩ=˜ 7∆¨î‡9µx›¥>Sâ\"”Ç4zˆ”˙“¥0f\"`1Ω∑:ÊÑ∏x:Ú≥íÅx;Z d∆÷îÌ…eµKÄ;–@“g∂TML¥°7N3RækôÄæN(i‘Fâ;)ÄOrn:”∂∞\"‚>é^#Îe;n›!Q\0†/‘ÜfU\0\"˝@Rp6Ú†à®éBÛmïÆ0ÄÛ	ç∑ê!€YÌr/TÜ tå1ôV…eOQzBr—9,∆@9m”!ñ›táºÅ11;Ç\"cmMu,SÊÂ<≈ÆW\"Û∂yv°K)V‡ªmÉV,ƒ”b®˙k‡\"‡1Øùµhx≈»F&Ö‡Ü»9πtÉ“?‡‹ÑIq∂†§Ô\0g¯5ªMKù!,ÑÚ?¡Â!PSQGˇeÄ@ö»a:¿bÉæHd@(: ›ÓüDdÜ‘FJ1HÃá 8»w&˛„@‚çÖ'iπVmLê E/Â°[RòRÀªZın≈f’èßﬁŸ¥_πG‚ãhl§b≈áãª¥üÙl….¶Ô¡é3%IŒÔÅé[c6é 3k·,ÄIDÖÌÁX%õøJSoxÍvÑïK¯B˚q[iDÏ açn˝O@º€±m⁄’/Ë|ÃiK•2RjfVr±ÔZEJ≥nôg\0_∫HÍ;PHD”n„-ÚïÕiÔQ∫∞a¡)å)ØPg€·ÎL6ª±ãæ%lª±k’:Ü˜s®k⁄1ﬁ˙S©m”NFΩõÎo¢(ûÛSJIe:Ü¯‘∫JØæ=lªÒÔFÊÚ¡)Ô≤ã\$x‡¸Õ£º>Û˚√Ü9ºXõ˙oº<˚'∫ mHf!ÒÔ£D;é\0·f–SAòÎr/„ê;Völ<¸eöµ±äØúªÿª2JŒA\$¸£kúU¿å¢ÁœƒYØÖöÄ⁄ ≥	d˘\0◊ª|pPò!f≈“Z\r@‚ì¿¶„íO'_x6;\\4ß%¥Z6[Ç6çÑtäâ K≈∞#”u1|Ó2ÖXOo&ﬁ6~•±Dq‡÷O<›<å:”∂|é≠ñp%%Ã÷≥R–&\r¿*o °x\0C[ ∏#∏Áı:lpw™Õ\$KL»Ä;shÉ`‰aRnàz‡;œîÌ;∑±|8L™œ‹ÿ=OE«•HÜ* )ö-ïT™/‚õ≤_ÌH._%õÇˆ„Ñ“HÍØ¡7THŸSD5>äÙ∞SÀ_µêcr~ÖyÕEÏ\0“*‰^∂˘ˇ,Õé¸FS∏Ë=Ü\0È#®>¶Â@c∑EÈ¢≈‰MA7oÿ\r†R∑ÒqΩ{S©‹Û˛’¥1›'¯òÆ·'mπ≥¿ç{p0f2/ºÃª<m:-H∆∏¬òå%N'[P∞wdÖÅfÕ‚\n¿5<tà∂	<nÖö5F3ÅÒπ+®b°è*œq∆edõó∆´0îµ@öS;∏ÈûídÑ∞ÇÏñ¿:Ü;=>ÿÅ=Ä–÷#ºá≈Û»‡NŸR2œ`ˇFÅ∆dõ\"âÉwQ›µUN´±¶¿RÜÄÊéØîutv”°\rß'¯K#à∂Œ˛≤O\0…∞ÏîëœÌrî◊'ïrÉHÀ.|†Ì;O∞K.8»ÍøL≤Ä‹ÑÑHT¿|srûãÃÉIMr§˙/4<‰è¶ﬁ<îr™Æ∆ñ0Ï5nˆ∆€»ln,€l?ÀN¿‡¢ÉaÀf∏‹†Ú·!f∆πÚÂo~¿|∫ÚÒ∞∞@2/ú¶¿<πÚ˚À–%|≥Ú…1~ÚiLæ	_1 5Ï'À}Ω¸≈r‹!Ô1¥6ÚˇEo2º∫sÃœ.‡ïÛÃ \rº≈r˚À˝7<ÃÛÃﬂ2<«sn;r5*O‘˚lLD∞F@ÿl[Õú´;Ï]Õ¶∆HaGw7º5∂Y∆ÃöÄÆ∞5Hµ’\$≈A∑™\\<t|µAèRQD§„!D\\Ó„IπqvÓÄö@‡}ßå˘Œÿ_˛ƒlhï(Ò¶≈·SKâiœ)|iwT˜¬í∆∆ŒºÚO>Ê,a√ÒÙñ>TôŒ·4|ãs÷d`#y\nu√,ã‹Ú\rŒ∑@Aäsÿ8è=‹¸tìdÒ‹YÀw&ù		NﬂBÒhúR„Ï2™t %üªq,Tä˝Ôé°wMòÒ[!O=˝Î≤x›àNHJ@ÉC6ﬂF›É∂~¯a\n.œGªqï\"'(ìÑÙãq‡\r8CvhJ`9\0/„¿¥|tñ&w@·&œ“+Î\0áÇº∞]ÛπÀÏ<btâ–◊?úDqO:ÅÃ›ÿ</aÅ XÇ!Å8¿âYE—UîEÒìΩ“lº›7G=”—aL tÒ”ˇ@1^Û€œˇ><Ùç–áQıX∞ΩÑ‚‹Ä	\0«}?Cw‘ú©]Ç=—EWÉüå•\rÌÁ—Ì‡‰ÈNRÄ+ΩN‘Õ#ﬂ]?ı1«oG¨ˆÙ}.wG≥Ô¡§}%◊6Qü—Ë¿ë#Ù≤@.Â›h;NŸ <PÂ’‰ê†1Ñ‹8àtœŒ‚°º˛uüœÄI¢≈M–·!]tU÷®D¡7q&0‹õTêÙz¶˛◊\0∆qHÿátI¨|`6lIe∞R¢ãêÿ¶M•∞àü^†.ıà5ÄƒuÛe^ÜM›ˆ◊_\"ΩıÛ7_Ω'o+¿Ë0—”s◊ÁQ›?ÜFçÿÄÄ∫´H°›ãuÓXR+Ä¬óJÃ¢O§\\¬\rœ7`éNøˇ|Etœ–H˚ΩwºY„_Ñ›ªOa#b@≤ÏG“k’ÿNˇ¿0Ùú SÑΩã∂UùÑı»∏ﬁµ◊h7=\$å_⁄0c·xıå7e2qtDÂ°pñÒ0ΩÄuÛmœb∆ÉÎÿ±*]ã^Ëùç%¿7N›?ˆl/P¸ZÙÍwO=∑Ç‘gnV‹ÙÛ€≈ù° vÁe’©wn1a^]¡ˆ]<p\\˙ﬁú€‡¬ˆ”œ&AzX4#ß›Ø7ÿØ_=Å—k`–ã}ã!Eåÿ¡≤ıÛ¡^ﬂìÀKÿ∑t2_˜Cÿ∑\\IX3ÿ∑˝∞—ù:%’ùÇw/PãQ–.ƒô£ÅG›¨ÜA?ï[”ó›ìÇ'›wd\n9p)¡…Éçr7vj[‡Û˝ﬁ@!ıÏw^ŸR1€⁄§7[÷Ñ››«e;ëëèqÔm[´wkÿ¥µ∞Øù/5Ÿ¢rˆ, ?bÉ']≠⁄rßÚ[\rã/5⁄›∞™◊Ø∑r¿.ÓÒ%ƒU·Zv-ﬂlÖ6~ÀÕBıCÛ„≠ˇ◊»÷˝ãGçh¯áΩ≥S ƒougM∂—ÔÄp,◊O?À˜çÄŸﬂDÖdΩmw‡?]≤u!√ Ræ4‚V◊_!ßxIwˆ◊^¯H9_=>˜}·0≈ÏﬁÇΩ)‹Â=‘H…€O_=›¯Ädÿ°å¯ÜÌéùÿ •›dÉ 3˜ﬁ]¨π›˛öÿœ_6:xèDx7Bã≥”º\0‘]/»=÷vIµJ<A∫›◊XŒÅ,M◊ÀeùÙ;G¬õ\0002x|&\n“tSïΩ◊Œ‹c4uˇ„}¬¯pv]\"9x_Ob‹'S˜K•Ωã(·KÿaﬁD¯X∑ãÃSm›«çÁÖá·Ï3ÄÁ‰ß_<;ùœTΩ˛MÇ•‰äÜM˘\rHoãæ*À„_Úìπ—ËÉˆ-ﬁòRú‘Õ'N‡U:äÙ◊¨Ò“Hë⁄™í%\0æ5øg-tà\ri≈k5√¬«\nØ˘ò`ÂHs˘°ÕØis˚~qOoÌî\rç^m>\råÈO˜/f’&?wÊ‘ı	›˘r‘3*}ÓtÛµâè›<ÜãÁwO˝Å˜∏\\e…V&d%}!.üﬂﬁÂ{J™ˆf◊ù&Fx∞8+R™Ô◊:ö¢Äå˘ã”,x‡Ä˙\rÿ^¸ö¯2uﬁ‘V]ÚqOs]¯ôx=#C˜I}¨˝e›Ó≈1b“öıo“t‡JüIÊwPΩ\$vÒ±¥Ó<Fpó<¶ñÚyN§\\∑\0 =÷ƒ‚‹+5√¬Plõı\nSª»≥˝ˇÎ”æÁ©\\6ÌÉøv˚ªiı√9q4ﬁg#ÂÔ™|˘ù’‘§vÏz®K‰ﬁΩªµt≈/›/ÎØ‹5È“ j_I¬ˆo%!7Iû∏t±ØQíƒ„kÇ\n»M÷wLMÎ≠‡ÈÇpu!0KéÅ\n/WØ˛∫¨]⁄êó¥Ê[ÂÔÆ¥jz˝∑É∂)ô‘}\\XÌÄ)”O˛øˆ>}¬ÃSïÏŸ8¡@\0÷1óG˛{?ﬁ˜yU˜ aÌGû–/∂/¥K~®KﬂÌá-ÅúO_7µ}ê*´Oaæ”{z%·Û≥œ/∏¡,‘µÌ\0#›S˚ÖÌ=¢`Ö7Ì®aZLÌ∞˝ª{åH^ﬁ>–pµ¬7EOÓ\"‡¸˘Ü©/∏ﬁÈ{”»‡=TNÉÎΩèÖ]¶÷G.‘†≤Ä¯W∑¶‹◊˚„Èê?]àOﬂøvLVF ù˚{ÂÔÌ›¡J»ŸÔœTmioˇÏ˜®‘@úÈxO¶?z∆/0O›Yı|.Pûb|+€èòùª∆\nJaWΩ/üƒ+!ØÛÏ?ŒÔ≈\\Vó_ÏW™Ù|±’=∏ë«äO≤˝ Ü\\˙Fâhab“˙oC∏«@Ï…OqÁ|Ö˚\nz¿ßO¬2˘Òáj=E¯QÚóIó˜ÈÒÏ˛ùπv07ë4ÕàÕÆ7≥=≠zã›ﬂø2ÿM»◊Õ=)˙≤M7[ù!ˆÂ›ﬂj∑ÉÑJ	ßÀ}º^ÊwÕˇı≠Ûm·hz«x9‡{ÓÑ›∂MüΩπ◊Aî€|ÎÏÃËˆ˜Ù≥>oÇ)Ù0÷˝π∏\"ﬂBx}40ﬂKÏ(‹\rm›πykﬂß–1|”OnPñt”Ë¨îñ{˚ïºéÂ‹CqÕ€è÷\\d˚1“ü”a.uÙ∑ü]tø7àcn„»YˆDEV—€d«Œ5(}ê	g◊Ωu\rÛ◊ŸÂ˛Ÿ<Téø\\˝ì◊\$∑c‘´Û*T˚˚˛÷ú;ï[∑´íq{˚ﬁ◊]U}’∂	Œ_t…«„ç¢É5ud¿Åîkmq_ﬁæ7µKÜ›>˝+y{Ñ›&√a?‘>N,º¬5ÀQˆó¶^±˝}ˇ—°.}aEr>ùÁ+ÂÃß@‘ÕÍ◊œ›nÌ„Ï?ce|X}'oÏ¸cÒ´e_ïvoÏÔ{ ˜}-˜xø~SÂ_∆õ =˘wn\0;ÿ=∏¡|‚À'Ï8çíˆ‚‰']≥ˇ{C√èË˝n¸?Êœ[¥uIˇ÷±4¸)~üÎ“=ÖuM·ﬂÎ=Ω¿1Ï]>F\$sRèö¸™ØÚOö‰„\rãÚÂMw{∞/Iª‚]ÌÏ?≤ï5Ìì˙º“ıNµÅ˚Ëì˚ÑèÌÿ6õuì√‡Rø¸yJá≥ tÓ◊Z\"»ŸpÒäüt”⁄¡Ó›ŸˇV_»©öú°øf˛s€áoœzÅ÷◊Û”fÄ…π∑Û¡7Ã´ø˚fV˙ùAY\$™´Á|ÛÎz’Ì[n¶P;•¸[˝\0ü›[…˙/˜ùeC“ùàÄ¨O)wﬂÚU»BV∂WÔû\\±¸ÉÁ‡p‘TÇé]àÂÿ·ô_ÎÙ®	ØG·ä˙#”_v}ä€5^È–Mµ/ÛıÚáıﬁ\nêD≤®‹dŸÓK≠\0B≥“\$˚ÛöQz®•j òû≥ìMD)5êƒë¿4´!V¨∏§\"ÆXÍÃ»Uêé\$˛\\!W:©2O ¶¬pêç!V\r9bıÜ ∞	Ù™§U∞â1J\$‡.™“Ëêß §Çûp—@.‚|ƒ˜ìè\0TΩòY⁄‘)†èå%ÿàV\0¨ıÈJÀ…fí∑s\0Rä%Âf¿pÄÉµÄ4\n\0‚òG´ÄñV¥Ñ¶¥µjê—1¿AÅq\n∂e»¢A§Ätm4≠ôä∂îO)≈Ay˘‚?\r[¯^tTDπÀ@^GâyáH≈ﬁ◊î@i%≠–ËË\rÀY‡8¢°+ ∏È_Íæ(Ÿ†E,dW<@WÍΩ’«Ar†H_Å\$≥Ò;|	Q2KÑñX¨-‡ùB‚Àµw‡È†PÆCYD¥2π%¥ãWT¿•]2säƒo¡Z˛ÅT∑@)jurKoË-Å\\L`Ë'Z˝/\0ÅÖ…nÉwÅs§å‡a¿¢zñz¯µΩnk h¨lpÔUa©K‘W6	\rÅöπ\nÎi•ÖÙ oÆôrVö¬–√◊–9``:Æâz(.h∞◊#-ÄÛñ<‘Nê¿¿lÅ©π[PÄtK5E°ØZxäÅd®p\n†◊+Å%‹\0	@UtixÄ˝´õXÑp¶j∫7ÕaO’—âZ⁄hΩ`NU∑Ç≈Å®)˝4“≤Ö√àÉQk(ù†Mº ¿YbÂ÷-√DÔ\"ËÈº®¸8%@	‘GáÑÅ‚∏.dÂbƒ¬É¡¢ü.\nÆzåpÀW´9“g&À∞üCÊÅÊ\n–≤^gä´äi7Äı-X—óV\0Üœ\n {”Ü,mÉ’5êj¨1UkÜ\n≤˚ì£_ˆ¬Òï`-,≠ÉÃ5e4Àıó∞yÊ_\n´ŸÇ2áÇ¶–πÉ3Ö˙°Ó…ì@\$lÍœ‡ô»Faåﬂ†Ω2 f¸¬ÌÖÎ\"®,Ba¿-0»'F»çï.h-•iW≥~Uå√⁄¸ D“ä≥À`ñ∆—ú!V84Ãíò\r¡ñ+I≠íë5Ê&L/Xó3_ƒ–%û{ı¸E◊ZáíÇ§ %ñs(»6¨MQã9ÉÜƒqÜYtfå®ÿ∫¡∞&\\M§â:86\r‡∏±ﬂ_A~X\n\n√Xô¡∫É®M!êÏ5˛Ãë‡ÿ1pÉé≈Å<v-eg‡Ö5ìÉ‘Õ›Uk3Ó\0Ä%?Éﬁ”<†êÚ-</æd¡º<¯y@ÛM‡ÿ0-&ƒû‘ V°h	È¬\0™æ|¨c?h6c`˜ì÷+sÊ\r∞&!0Üö≤gj_¿=Û3Ç∑Pá ¥√ÉªŸî€CE3P{ÿ¨ÄB\\&™l(7@⁄QØõÉﬁ’ﬁ\rÉñ'0ç‡ÊB'b†eXlˆ«Î€√ŸA€'N2{≈¯m=⁄x≤ÈcÄ≈*Î>ê	lΩZg4Gg⁄«\rè)√\0	¨—\0APÑ∫Aû˚%≤Í–j\0\"ÄGgﬂ	âíìF/ÑŸ:¡≠dé…yüyH6åóö¬kf2øàúdfK–r`œB=ÑÏ≈∫{=F~Ãó†Ô≥b√aUt∂}ö!B≤Ö	é&à<ÃóöD2^f[µÇª%Ë?F	±/∆c÷∆ÒíÙ ß¡ÂY/B	ÖãÎ\0(AmB°?±ûÑ\rt*÷pl»°Y\0+Ñ4≈YíÙ!ËV∞àI≠B#Öwö€'òEIú°\\¬Ñ^&ã?(M\"ó√ÑÖö…z”∏ZZ⁄0\0MÖj÷\rç¨-HW!4BC_√	 Y;8Ióòﬁ%iÏ¬¡å1ÑÃaÿ¿±á'zø]ë;Ü7LÃ	Í¬e*∆∆Èú¢∞¯+∞la73ˆÖÒ î/Ë_pùX›A≈cÄ∆Í‰∂Ãna}¬ÅÉ¢∆r‹(ÿ:¡Ï¿´AŸÖUç‘1&&ÃÅaå¬ê'F∆Í3¶ën°K≥/`Æ∆Í‹®S∞X9Aˇcu\nä1à_p©°1∂c?\n±ç–UÑaè¬˚Ñ(∆Í≥\0ËhÜÿ€B∫cu\nÒ¢3&2lL’√GÖÑ–Í\\4xXÃa!d±∂jÓ∆Íå4¶V∞g¿)¬’Ü≠	˙ƒ-∂6–∑ò!Ä+Ö¡	\nk¶7å#XﬁØ∞6L›ç˘ZFäm2‚¥Œ(V¿µó\"ˇEÔ∞uX2≤\0c¢U≈≥É5&;ç,X\0Klíæ`=32VB¨÷⁄`¡gjz‘4∫Î –	x◊∂√*É˘\n~¯yÜëÑZìØh_t‘5ó{/`·\$*»^”	Që\"Xôø4üdÑ√h÷§!¢∑LÌÿ<ï∏f§V@ö√vÕeâ¨ì[b5⁄É!∂G§ÃX^0)Ñÿ…ˆ<&HêÒÜ√/Éπ∆´D˚0 A.7lá®˘FÛ˘T˛çıü!9¥KÚßëæ´~¿ÛL“Ç–\0¥MyÑ\\=ó*[ú0.∏wZ·âö˘ZkeÃ3Wf¢Uò+8∂Ud˘⁄2˙_÷«¯úìWÊ¥-”˜SP∂YµP˜≠¶ÖF*mH	,+6fRÂ—Y«3a'Âû#<’a\n´UWB	L‰Ù=0ñ…º\$ó'R»≠|TVHÏÊIÌA£Oz≈ùÛv}L˚ÉÕƒ4ÖŸŸÜª?vÌèµ4˜gÓN˘üÃ%V.Ì\0öD;lzÃ`<ã	ÿàÃËŸ–DKgQR\"s9ËWQYﬁ4lzŒΩùõtÏÁõ±Äh†’uzÛ&Ëcçöïπ&\$∆ı™cDˆä!Èö04^á4M!£35û%Wh¥lbZ]®™Ï?¢bÃ˝Z{⁄'‚“1û\"ÿ>Õ'Ÿ£ìΩiŒït∫#\$h@IWZR¥ß.∫“§°YA&·‰ô“≥≠_\nŒ…ùÚE∆eL.Y≠ƒáH∏Õ,£3N¢tk‹Z|'i˙”ü\"f 90H'æ‘Åz∞∂£0“Zâµ)'æ‘ÆLkÕèòøì¶á¯“≤§BF±a1O\0õÒ¨+ V&·È	ò/ö*æ÷\0û˚XfW˝ö≈0à∫]ê†[x?M)¢±2ÉF¨!≠fá≠iI´\nÙ‹§,‘†≠Ã€ô<‘0‹ı,X]ñËŒõ°\$tÓ±ÒB#t¥’≠‘Ï∑SHhê»¿ãÑ™Ä~\\\\*J)›n¸D¨NìD—º∞nTˇâπbA¶Ê	Rø‚Óù!{u√œŒ)€^∂∑mz‰E!€ntŸfú[{6ﬂq÷§Ñ¸¢yF‚ç∆Äé∏‘/Ñ\nÄñ÷\r¬B:<I\"‡\\*ÇM¿hCó[o>ûf #Îpón	CµK¨	q<:`6ﬂé‡EK4\$,ÿ©∑Ÿ@¿’‚wUQ∏4T\0ãm∆‘àÇ\\ZD1Ÿ∏3˛%>`JÖÒ≈1oëπq•{/Úì|∑ôkr·Ωc“ká‹j|üq∂oX‡ë&àaFæ±YÁ%4ë~+\$>‰È;^ôˇ|[1€w8´Ì‹÷ƒvzÍåòC¡ò π@é7äo»:‹	H·≈!åî≈≈îã4‹`\r JaDâ÷Ê|\"ﬂMØXPpÕÁﬂ‹ªÿãNﬂYµhX¶ÊOÉåRÆ25¶°;8){Ó(¶>0ÿ•*A«Ü\n)ÑÖÅ–LΩ-Æba/wûÉ0ˇ±Õ˙JG8.C\"sÖ1mä„®@*◊TÑ¿àÅ\$ˇw>˜∏†L⁄!ûMÉÆ}ﬂë6ã…‡WÈ¥Uë≈ËãÃÿ,l=1!QhÅÒ®ÃÙ/û-àë†iì≈â\n¯¡<_‚6¿GFÔFp‡›:Î≥øëÇb≤6-å‹â*Ä„¯–7[V∏†qH‚ç¿:Gˆ∑ŒÅFFqJí‡‰\nJRóŒ-[’'∂Ip\$)%√åd¬ÓòI∏…vôd¿¿ Èâ£„q@˝LS¢…á‚È/◊ E†pÜ¸1‚…ÄHˇ„ºSN´y‘dM¿#F=Y⁄ì˝≈ &ßi_∆Q\$ﬂ±∆Zû¯≈Aé‹ddr∆ºld\"Œ~£!ÎΩ;n‘ÎeŸ{Ö–éQôﬁ†∆j	ÄìqÔÎﬂFˆŒåsFrwR·Õ∂RÔËŒqúQÆ·qêÓ**œpJnLÅ7 Yå◊ƒ'˛±†ﬂ€FnÿÜÓ,^GæF\\ﬂ(ø◊{Vıππsﬁ§‚Î¿‡º;L¸€âhRä»”Ç´£K∫®J¯í5“ó¥‚ƒ#C;>çSv5[Â∏’Ò©#Oñ~øÕ·‹TX”Èé«eΩä@ˆ)ƒ„4πÉ°ê›¨VçÇ.¡8LTUòç≥¬ˆF¬çíÜÓ–LŸÅÖcdFƒ|∑8ål˜ÛÄãci∆ŒT~∂“6‹k–ÌÊUqn∫4|n7ø¢jR≈Eo2Æ¥!‹nñÛ)Ì\0ÿ∆Ëç‹¯!g4@c6î˜∆˜ÏÆÿ',oﬂ–˙÷=6ÒéÆB8∏÷¿·^ˇ=‡mÜ20±ö£§ßKxF!≠äT»¶ÑqX‚Qu!ıNÅñ¶ƒdh›)¡‘?jtB¢2*(Â.µ¿‰ΩéRAH,Z '≤QBé«&\0÷õä9°◊¯Ê—Œ›ÜΩävÈ4√ßx®∞˙£†∫m û:=xË®nﬁ´A&éêÜÓ:[§gbU£t«QtNﬁ∆:Zón1Q^ù«[CvùpSÒ(Á¶ÆùòìIˇ8X(ÎÕÅ*åé¿∏€ƒTQïéß#î∫›égÙ/ã´8›\"_øiq;‚ƒG œ¿É?ç“ãıª§wÿn\$c¥>Pè˙mÙìÙ»•U„ΩÄdèÙq€§x«IÒÂ£¡Gòè˝âΩºy∏ÚÔÃüüïi	ÖPQÊó9Ñπ—Ò¿Ys¢É¥æd\0•]<«±ìÃ)”¿LÆc€AXıˆ/[p%D@J£Ÿu|ziA;‹¯ˆÒÒ£‚Å,u∏¯ù \\|®˜¬¯’ •~CB>ô8˘`K#Ê∆ÖSP€âzO˙QÓ\0¨Ç√W*Ì›M~E…\ncﬁ«∏#ÊÊ>≤tã1˚cÊG€¯	IÍú}ò˚±˜R∂≥x¯5Ò¯¢ÑºØSüT*XM\"n;J\\ÁLÉÈ+\$˝X¬®Ã≠ì•{1kü‰∏IJ\$\"Z@ìΩuØÔ’©8_{˜âuƒk≈.‚ﬂt«›èﬁñ>≥≠ã2	í≈«Òäëd‘PS QNg∑vÇò˝«0æs˚±jdÄêx˛º_o‰≈v\\¶âmƒí∆®,FQú¢§•Ô|vü—^“SY	Â c\$ˇ|v≥}Ømß 2«ª„mî∏Z9&¡¯≠ﬂ€”-é˘B.Ä2H‘\0Ä=<ÿê¸≤Î≥·‡Dd3πbn¿&ôl´ƒ◊ÄJ‚˛≈˙m>ì‡ÿq#– √˝H\\j	4ÿ@xûC∆C∑zòSlÑqÄI∆?Í\r	'í¶¥õÍ6GtG!J\\^Dk≈ß@êbáè≠ÏT£u'˜(»â>öƒ‚„˝‹™j±R?›Â•HQæºÛ2À¥ÒÅ⁄üU'ê]Ÿ±Rlã¶˝/+ﬂ1∫/O÷û‚FáøÆ´‹¶%}Ú¡“:iG&¶îZ‡\0nˆ	ï˚`)`aÜﬂ∑\"fQ‘ã˙wˆ6ﬁÛÇ~*ã#Ç1Úåµ8Å'§n˘ëºˆx/dc∑ë`–WQ>Ã˜©Óê™–á≠â·≠‰ê·#‰K¿ò¯NÈ\$N∏!O(¨ê@GÄèy‹¿æEí\$)F[{!Ôï¯ªá÷‡T #Í•J©|âïè∏ºA¸X%¯ÃoóﬁºÆw|∂±Ò¿®Q4nì¬`ö·ë®ÁQÎc¯eu©s†2πÒë‰#\\ìg‡Ij’ÏGjj+Tvß◊ØDcàI*x˚¸0¢çî¶éÛ2\$À}jü™?[Ñtçq±_\"Äh%±Ë ;∏W≤\nû¿›#Dj&ô∑L∞DœœﬂtæÑÇS#F@À¸'óNûKãùpì≤EöË˝m—\$cIQíË€çΩãßó„/MR,…zéaÖ4ªßò±°b‰…O∏’‘R{œ‡û)«Lyë`sÿ±0E#‹äêê†˝”Àÿ)2Úù?ætK|†¿åÖgOÔ`ü:E•Å&f=“ hÓ\0RR;uvrÂ‚€∂Ë#o´\$∂9Iìg#RKd{¥Úéû]œ…yéå¡ŸÉ^Y.èœﬂ I±é∞M;ß˘\0ÔÜ§∫'P^‡<#÷6‘*Ì›∑§|∫¯ÌÈÀnGYI·S™ØX¨ÙïêtKüN∑Æ{2Æ∂HbôUD(ﬁ±…êöò∂3Ùii!o˜âMID|¸°Î÷ŸRzﬁë'w˙˛m≠»G 5)¡O?íZqÜP+˘µß\nF%∫?W[\"¶Ph˜π†W•\$I˙'¢àô?Óè‰TJî,˛nJ°˜G‡Gı çˆË°Ò\$ÿü¬@ﬂ *ë∂‘öTßÒ`≈RÇâ“sƒ	ÓQâG8˛ÚçeFÀîT∏™®\$y/å]º^\0‚ŒR3§Ôoiœ#÷))F¸§ó@n‹e)/Y=#RRë©Â¿+`c7»ÜT›‰`∏?ƒËiBÅüFÊv§πÖÈ¡Öí ô%'≤Q8ﬁ@/‚*˜„œÙ^êΩ4îÊ¸±ÿ§z‚	=\$Âæ}\0¸M˚<ZŸO'ﬂ\$©ËmBı˚ÿ0NA\r>ö®¯µı\$ûgJod¥+ÓÈMﬁdr¶‹2Öª€è˘(–a¬ZπS‡îüÇ1q9⁄?Ï¢âBNMRt ®ï@¯Â‘˚_âU¿î§8\$˘BX≤b?Ã´a¶ÕØ\0å>ªuBûäU∏	WÆn\0©%ﬁK ‰>RHNÖ£4:ÁI¢ﬁ8}\"x!u‡GÁI=ï£)	◊cÿw¯√ùî•Uï°ÅeãÄ(±D}@∆“J¨Íˆ4cÆL“1“lÎv˙?W|äT›ï^…˙ïﬁÈeäBiœéPñŸìı)˙O¥¨ˇ.ÖSÀX-'±0–î†ú†…˚NÛ .XkÓôb\rä_ÔÉÿz|úaÔ¨∞…bí#“˜Ωùv@ÔNX”t∂™#%ê»ˆ∆É’Tzµ[§≥‡áB∏™pñzÆ2Vd∑ÅB!øVhÉuTîIJ»U|xâH\0BFÜBœÄ@Ω\\\n9√FfI §Ä§òî‘¬X„!\\Ëñc,æZ!-CÅteö	Ëñ∏¨‘îî»	“Œ’ü¿(U˝Pí“¥YjR—ó•+LD˚\\µH\0j‘’¶¿\\VÂ,∆Z§»¢FB£2V‡ºxx»Q7PQCÀ|%ü-˘q`êH´÷Àãîÿ'rπ V“Z]ÏKçvñ)_\\π)q√˚’Í,°ó6oå. uvÚÂûäÔWwÓ	\\∏∏›ã%QçÀìÓ0,∫ƒvr‰÷m;óe.y⁄\\vÀ'é¿t^?-≤™9®aI•√¿it#î∫…q2Ò—›;>ó{ [∏Å°“‡ÂΩÀƒ4å…˛…˛ï¨+ÀeÁ¡@^è-N^0©yíÚ`§†^§ˇ¸>Ã∏)i€®2Ññœ8ùHE-ŒB`c\0N%åø6{∞»òˆ√XÜ9Óƒ0»iló◊‡1Ω&p«é|%–	ÃpÑƒCùá6¨1ê[\"uÖ“–C\\eŒVí»Xwl‰·4ﬁ&2—ó\0XIÃ¸°/2˜e·Ú3\0µ¸15WøAËa\0¡aT6Vã›0Üa0Ü`„YÜLf0ÖU\\ŒiÖ¸0∂rLZXB3¨`y0˘Åzˇ6k· —±–ò.N•¶Y:í0sX§3–àAúòÎ4 ˆ,eYÏ3⁄(RœÅéÎ¿ˆ‚L\$ÉœÈ£‰,HãlÌ¢ÃkgsR\"ƒIò`êo\"0]àƒ≈#C∆É1S4:hp—	ûÏ6¬e—b41√e∞ôíUÿç∞°Z3åh–≈\"4GÜZWWæ”(Vï•1V6ç!YT5Aà}˝Ék+¶¢ç&°9DÉ.åU}ö;Üî-*∂îi∫&\$·U¯~q'bPDû(ÉÑ¢ZFü1*	ú§ñî=ìPrÖq,Yú5gÚ‘zc≥Q¶∞]CÃÃ»d»Z&RvÆåªô0√¶VÀ˙#ÖÛì\rçj5âeP]çïCX∏õÍÀ∞Ì+6§	∏ÇYñ¿¢¶‘ÙøHH<a¥ÎjêK\"„T≠€PX±®™áÖ?aA\n\$ƒô˙1ï&õi…;)7®åH˛.2'+iTÇ©.\"∂ôl\n⁄°»§VêcO3çñ6ÒmhD/3n4Œ|‘Ê>XHR⁄}&so…Ei\rR◊Å+l%!ªp÷„\$±‰˘7N®ì¢0É^pøM€†O©E-zíg√%Çâ∆1Ì!kãGâU„∂«ßsï™\0lz«9“ﬁB.KÎ/òà^=ÚM7fÔLﬂµÉrFÁ|	l—ó‚ÓŒˇ…)OÃ¸ZR#÷7r#äL©æ0)îÍﬁ'ìÅÑˆ\"â^a‘ïÙS*Sâ/◊ÌâJMbuíìq‹!ÅíWˆﬂ	ÉÔs“î&Fºï—ëé£Ë∆ùí8•“3øıÉ“(Èæ9|ÙˆfQkÓÁÍéù*â∑p≤Ú5˛õ≤	t*Ì›Õ©≠=3ˆwù\nZO™©≠ƒ‹◊õS\\‰Q≈5êõ°Nåh‘˛ÔÏ#B-õM+ÆK≥”ï¿+K\$=ˇv£6ÒÊ¢9!ÄK[NH'ZXÌVOD•;≤é&ﬁMëåBÎFnTfÁ„oÙ“ùKŸ0˚Nõ∆°È¥U2æ-êíÕ“Dì◊˚¢ùÍ∑éõ∆Q‚n\$πe%Ù@ó•-Œ\0,\0§‡’âé«%È-ZYt)f*ÃÂù+>BŒ8f*§E“”¡wKQõÎ8N<∑B\\––¸Kv\\;7ÿîtM†Ä2–oLG–N‡…eÂ2ßG@ÖVËÎÿ(±–@@\0001\0nîò§‚0‡\0Ä4\0g8§ú‚@\0¿êpZ8ƒ⁄ÿP@\rßN\$\0l\0‡∞Y«≥ä¿Œ+úá8∏¿y«¶‘\0Œ4úñ\0¿§‰ô«≥í@NPú¶\0ﬁq§‰©ƒSé'Œ@ú¶mlêô ‡@N6úF\0‚qòiÃ”å@Ä7\0l\0¬q|ÂπƒÄ@\0007Z…9pú‰ sâß=Nrúì9‰ƒ‰9ÕÊ÷Á/Ä7ú£9©Ä9»Süß.ŒZúo8ò∏ês†g?N.\0s9ﬁq4‰\0S•'GõPú√:.t|‚YƒÛß'N.\0m8Zo‡¿Á8N:úØ8ñsÍê@\rß6ŒMùA:™udÈπ÷3®g*NkúI9¥§Íâ◊ìô'GNg\0`\0ƒ¥ÎπÃ‡gŒ†úœ8ŒtúÂ03°ßRN8\0d\0÷rîÁÈŒ†'aN*úﬂ:≤ry«S¶'4\0000\0k;:s√sπœsûg_Nhúõ;vqƒÏy’”úß,Nµúá:Ns§Ó’`\r—Äcù6\0¬stÏ#˚3øÁ(Œ≈úŸ:jw‰Ë…Œ¡ßN«ú—9ésÏË˘Õ≥≠'8Œ°ùé\0Êu‹Í©Õ≥òHÏŒ~ùi;ärúÁYƒ”æÄNæù\r:⁄rºËi›åÄNaù\n\0Êy\\ÔY‹«ß7O	û-;wÑ‚iŸÄ\rgåÄb\0jísTÛŸì…g#ŒÃû9tîÏ©«\0@NÁú«:JryS	„≥ƒÄN.û+8å⁄ÄI„ì€ùŒÌú”;írÏÛ‹@\rßõNÀùÉ:bstÂ	·ìì'ŒSùW9ÊsˆŸﬁì÷ßfNCúÅ;™sÙÒπ⁄≥úÁ0œJúM=jrƒÚ‚sóÁO{6∑=“r¯I‡◊\0œ9ú—9∫z8 ìâ'DN‹ûÀ9≤túÚÈ÷S«Á…OWûó;|Ù‚iÁìõßpŒEûß;yl¯È“ \r'ìN‚\0e>Üs¸Ëyœ””'SœÑük<zx‰Ây…≥ÿ'µOvû…<ö}˙ŸÕÛ—ÁŒ—üg:F|åËi‹Äg≠N‰ûa>Jr<‚ÈÍiøßIOìúO<¶{¨ÔI≈ì¶ÁiN:úm<js∞âÚÛü'n-tù˝9V|TÛi¸‰ß¥Näû#9úåÛ…˘””gäœÎù	>Œq‘˙y«ñ'ë´õüœ9Bw4ˆÈ‹S Á«Œ3ù•;ä{D‰â›≥∞'ÒŒTû[9y›\0Ÿ“3éÁ'œZûc9V~ÑÎ)«-ŒÁøNIù%>:u§˙…ÀÛÂßiN¡úm<D˚!çÛˇÁ»O\\ûÒ:ÚsÑ¯iŸ≥√'6Œ-ü«:vù⁄3ﬂßq%†h\0Œ}Ê9Ò§≈'ÍP@üô<Ê{H\n≥è¿P4†A:y˝\0ŸÀ≥œ@œdúk=Ótâµ	Á3÷gêO≥ûõ?NuúÎZ	SÔÄœ†Q=™yÏÍπÂ¥ÁFNÜûè?∆rÙ˜9Ús˙ßYŒπúã>&sÑÂ9“‘ßˇŒQù}:x⁄ÑË%4ßcŒÌ†};íu§˝…Ô3∏®Œtûµ=vMY ≥ÈHÕ–D\0≈:JrÕ…ÎS√Ë/Pèûi:^ÄÛ˘ﬂ≥øÕ≠Œª†œ;~z-„”¥Ë0†ª:Üq‹˝ôÍì∆(\$N4°U<JÇÌ9˙3‚Ë/O∆°q=j~˝	y…≥ê'£œL†U;<⁄Ñˇ©«”ùßrO¸°õ:*Ç§‰ŸÕ≥·ßò()ú«>æÑ¥Ò˘›ÛëËX–£ú´=íuΩZ4Á&–¡†’:t™\0ì≥hSOˇúìB\"Ç¨ÙyÀ¥5'íœÜùì9:{Õ\n\0îßµŒp†õ@sT˝ŸÛSßg8N´°-?Vv<ˇZS‚®Œ°°Y@r¿YÙ3ñ'÷P|ùˇ>6àµ\r˘ËìΩ'oœ™úWCRz≠©””‡héO\0ûM9æs|Ôy¸S∞ìP\0m?:ÜúÌ) ¥2ßŒj°ã@‚}ı˘¯ÛıÁzN‚ûõ9Óvƒˆ\n≥˜@Nú?≥Dÿå‹˚	ÔÙ-gP7ü!@˛q])Ãî=' P˘¢;~e\0∂ÁsÏ'U–ÿüèBrvÿyÎî'®ÇN¨°◊9by4¯\nÛ÷ßœn†9BZqÃ˘*Síg—7¢S9˙Ñ-\0IÊ'/Œó†;9Ç{DÛ Súß&O˝†WD⁄|T¯j\"3◊®PÈ*a;ÍÉê4'7—dúœ8˛äı˙3®g—ì†=2r9πÊ3’h±Q%ùÁFrs≠\0Ÿ⁄tDßóœéûaBÚxî‰	ÿÛûËŸN0úÛ8∂ç-	‹Ù_ÁK—4úY>ﬁÖƒÚZ,Ùk(U—èûDæç\rä0ì´hQ≤¢E:∂Ç¡S\n3£Õ–X£=	(9˝≥äÁ¥O;ü#;rä‰ÁZ\0îg≠–´°'DñãÏ˘€î_Á≈OhúYGjÄôÕìË,Nn£O=bx]9¸TUPQùè@6q\$Ìz~ßI—ûÔ@^Åµ	iÁÛ∑(´œ˚ü√D Ä})˛4d)P≤úTT¬ê¥Ùπ“4^ß–\r°µ<¶à‚™)¥>ÁëN˝£FŒâ-!:4NßPŒ.§>‚{îÍÀã(–wü˝CÇÇ≠IÌ3¶'&R!úcFÅ‰ÛSùË}Oì¢”>*ë‘ÚYËÙ\rhÓŒó¢Xäqù\rZSâgÿN;¢IEñÜ=!™3tÖÁ:PHû«DûÇUˆÁt=ßV#≥M¸‹Ór\rY‚TKË\0—‘°o8™y‹¯È¸SùgÆN˘°WGÚâm∫TC®`P#ùπE¬~}	ZM‘>ÁK—z§ÖCr}\$˚9∆s‘∆ÂO%úÎ9ëkhJQ”§g¥Q/üô@Å˝©ÁìÄíPÇ§#H çºˆÍ\n¥DËîPKù5FÜÄ5ä\0%'‡QŸ†EÚsÙ¸ OÛˆßS—˙üë>Vãm∫;Th»Õ“^ù±B¥	-âÃ4<ß÷Qs•)>¬Äù IÕ≥ÓßÒPB£};ûâ•& ˚ßøNLûgDÇmy–t\\ÁÁ–Úú°K\"v§Í˙S¥IiL“2ü´@BsE	©ıì£ßıœU°≥:≤tU™T¨(√œMú—CöóÑÌô‚S∆)k—¯°á=∫sE)Y€îbßvRÔú·:*|â‘3≤)=ŒÀ¢á:2|ù–\$≥œ'›P•U=:y•È˛∞i9QˇùL\"ï•\nt((|“Ç¢À;\"ãuU√sR†\0®ãΩz√<ïÌÁ…/mb ±ôPzeÎ1XG/¬f¿Œb„7)ÑT–YZ¥≈'~Ω}ò√∂^,Oô:¥ªb8⁄Jö iåY·	àb‹…Ÿâ≠5i∞ôYØL\"¶∏ÃVã\"ÿ5–˘À√ıc¨æéR@JlÃ Ò∏…4<õ∞)†–¡üSiMnõ;änåëÈªCıfü‹àÄ:†S bªlà Õ5€/)ÑLB`æÃ	á˛ŒU~Ëy†ıÙ‡@ÃSÉ¨1%\\∆8Jløóı≥Ïa	ZöΩ4‡Z÷©–1_âÇñ&:6î-=òƒ#ßPí^ùSJ:nÒ\r)œ¬^_◊	yä˝7∂~‘ﬂWÓìåÜt]*ùÈtfö\"÷mINmÎ4ˆiîù`¢©‰∫lw¡‘<À∏]Òp¶ïk¬˜4~]PZiÌ4%Ç“»ÙL2FˆrD‚	º0ã&KL¸,«’˚p¬ö¬3íV¬-X->∂6SÅò∞çßÍVÇ<85‘˝Z•´iÉ312e–€É”TGÓÃÿäÄê€Q˚∞KbJP3nt‰¿¬µ–Qb†ë4™ÇD ‹±£ûÊWkíÄf+ÊYøSaUêŸÕãyväÑpÈ\0¬Øâ&;PçÜ16.\rô¡òójb≈bõß∂∑â;\0\0IßŒŸïÅÉ4`Ã«°¸T=&úLfú†•⁄¬Õö‚6\0¬\0˝EmDJÖÏ—Ä)∞î'“¿í\$83;,BÄ¬‘[`…3ô‘8 iÿ√÷√SÑ`∆n¢”¶å¶Ÿªó\0ÑÕÈõ»V Ï\"¿§\0skà1º≠öé@◊¥”˘ÑÂOÌ‹s_UµÖö¯ÅÖf∂ÎÚ/Éafª¿WòG≤~©¨ê}5ím#Ÿä”ßàJ&\"ùçH⁄ç\0aj\rTúâMP“§‚îjwÏ#ÿô6cÕC!•Ô¨µôﬁØ}©#M>§õ\n:ï¨á!¶SV©_Rf•ìRöïåˇÍ]√üe≠Ü‰∏3U&ÍT‘¿by`öU8°OCüÎ≤Û®˙Àñ\rLBxå3i˚3f&°J2'∞\nıj;iÅûõÂõ<˙}\0ÁC'8Àßù@ã+Jù,ÊônC≤¶ì0·ñDW°ß¸\$«•>wƒVâXVS∞Í|≤¬h‘ÃMñ V\"Ï€Iô'ƒ©:Vhü˚6÷é, Õ/ãf⁄Ãÿ\n≤3Val^°ﬂÖkMOïÄÍè≈Y-/Z®Ã)b›“\rM<	á0Ò¿¡⁄&aÒò¨⁄£’Éﬂ\0M_?Q©'p˜¥Î™KÄC©qT≈ãp ÛÀÓ‡ﬂ¥v™V…ö{TZi5O‡ÿóFf!U~ıC¯eURCØùH	\n%£çShUU*î™à–.™ºÜEM)†«µC™ÖR©√ÖÛÃXŸ5óÑÅP¢ù\$&ÜÖ\r\n™¢0çáQUµK7ÍåUJ*∏/jb'Í¢,(ˆu?Ÿá∫_ßUÜçW∆k¨Qj¿4iÉû…‡<˚g\n∞¨fôüU8^ﬂ0±ÉıXIÜ[óÈ”]™√M5è≠SxSJ∞Z…∞<Ö∑U≥Àf’˛ê«™º¬h´CRUåÖ9˙¥–™ÿ–’DfpMj™#46jå¶©™∞d`¿ö=Y6ƒ÷ ¥ab‹¿µV•[dUpöU±Ñ7Uı\\àD’V…ÙU bËƒq°MU∆ıW™1‘ƒÖsV÷≠T*ñ∞”…ﬂ©ö´ëT“ùK1™ñ5eÿ2ÄY&íÕÅ≥[¶PQ&\0ÃCÖóV~ØSrcQ&%’ÒdL.vçµQö∞d	∂eXÿ^pô!3U\"ÖÁnÖBä•T˙a}™¥cTΩÑ\r`⁄í’x@”ZcuTÍ˚∆é´Û*òB¯™ÉWVü4/∂uQÎL™íΩ™™]bZhµSIéì’ÉµUF\nïUB|UÑf’X¨UUâã{ÍπïWZÄ’’¨™–fà’ã=V´«c†y*∆å≠j∑Sú¨oY\n´à-\nÆl∆+ÃÜææ´≈d∂<5aò’}ewM™≤}Z:ØuaÍ¡∞'¨¥∆b≤‰ˆVïb´0±ôÉ7YùèefjÃ5ej\"√(ßEWä∞Ï/µU*≤ ˘/≤VY¬≠=e∫±ã˛k0÷Å[V¶¥Z∆a}’Ø¨C0≠î'rc˜™€26´qä≥ ⁄∑µ•YC≥!fô\rÆiñë’£ŸZ¬∑≠IW˘©ug\n“lMjË’Õ¨√ZÆÆÄZ∫AÎjÍUt≠_WbµU3Üõ’©√÷’‹6›a:vû+UUÊ&Ωé≥É™Ÿ0èkd’≠0\r±˝g\næµ≥ŸZ’˜´ÚYãÖg\nøµ°⁄óV\0&V6\rÖRj¡5ëkÀÏ´†¬Ú∞{Oxwı´+aTò≠àø\n\r”Bò2m<*∞A∞¨1TÚRˇä√ï∫ÿéSF™øTä\rÖbzxKˆ+xA∂™ÕU:±p\n∆T…ÔV3Æ\r≈o «c´’”¨{Z⁄∑5q:¨ïêä›VD´EMjqZ\n≠ı√‡‰VQ¨∞¬ˆ∏ïfäÃuóY\"U~¨¡\\fπS»uµd´òVbÆeV\"©;™‰ï†´7◊;¨À0æπ≠gJÂ0Ÿ´;÷F™a¬≥›Y’≥°∂Ä®QY∫¶%t÷—u\n3∞ãÑ¿euh7U£´°÷ë´hƒ∆•µif~ï¶+ö÷ö\rZq|K\"i,‰°	’ºÆœV˛¥ıu⁄‘5ŸkQ◊C´ì\\˙ö•\\˙«u›™ÂóV≠eFµ•qz ï°+ö÷ì¶≠]Úªi k,+£¬3´¡[∫5t:ŸUﬁ*ˆ◊C≠£]‚∂ú\n⁄Õ,*˝SBÆk[nÌmÿ5ïÄW¸ûÅâÃÌ3êyÜCkqCÖh6Ãô|%`äı¡‰ÎùêÉ∂}ê¯@ı¿@\$◊∫´-X:©sOv\0pwŸW∫àFV9ö\r3–Ú0J«W√&ü]|\rDPÛHˇó¬\0V*È0àò≥ˆ´ÙÊò¨ìçß0*•‚˝÷åç`Wıö·N‹ä¨\r˝~W”ï©Ã™≠Ø€Rû≥ì9:˚å5©Ã±Xa¨~•;pÚ-©⁄¬˝&Ω_8òÕSÄ…à≥O{N‹e{Ü?V¯ÿ\0ØãNıÜ´\rp\nmèôaW gÊO ›ÄÎ\0Lí…ú±\rg˜`vºúˆx,Y†Ã®Y`äø˚Ã’)€”5∞ZŒ°~ç}yÉïY¿/Øô\0ú’l¨˙1f«mèCÃ38'}_&¡€:9é13, ≥®Ø.ŒNü	4™£å§ò@¡∏inO@y˙`‹X∞õUå=ıÅ™ËXlÄG∞aP<õ<õ\nÏôhTC∞¡O≈õÜvÃñkÄB∞¶¡÷√u{ö} †Ø”›¨XOf©ò⁄¬ƒƒl;ÄY¶ça⁄´9=õu¡¿*XÇ&—[…¢Ω| »ˆ,@ÄY+b\"√˝>õãÁIÏ™≥ay\\)Uu9\nzΩjä”–&ù`‡uL¶õQ,·”˜±	a‚ƒUS\n»v#lH∞åÆ;b6ö’äªêêZXòb«O’±ÂÑ¶1l\\µ¨´8√~≈„&˙˜5ò√SÓ±z“ﬁ∫√P´0Éﬂ4¿∞¬2∆;)∫~\\X∞”¸g~UmñuÜˆ-µÚk†X€∞ÂcaîYvãPplqXﬂb⁄≈¬¬∞V-∂:J≠≤∆g%T<’tõV√ÃUf&L©öçO˚5``ÁƒH_ßc≤«„⁄ÕS≈Wœ®FM‡˚ÿàÊbc”∫âvœÓ}êvs–◊	Û’x∞ñ r»ì3⁄ã5Iºl≤\$•*»ıB“Ìn@1p'OaΩ≥Î9ˆ'lÒ™áΩhËPmÅ2{6¨4Ä(mOfΩ•CVÄlÁ´ˆÖe,Œz∆˝UVuYèT?∞J^ +:0˜Ã™\"0'Øëc±áUï(≠WÍ‡‘Th7X>™µïeWê}Iâ5è≤ôeXõ1°ÈjóYY®zN…\rñ{#vZòkA˘®\0†“\rÉTÌ5Ú¨∏Ÿ=≤‡M¢…£∂ï±UÇ≤≈Æ^øL	%óÊ:÷;ZŸf\$&ØHÄˆL\n*9Uñh9_·∫ˆñ¨Ä‡Ÿ’çk[a|A[¶a‡ÃUhŸvU£\r\"∏6*˘T™ÜDáe´Rr°3ˆsÏ\r€@∞è≥RLJ√}ïª5V7ÏŸY∞Ñ=eÊÕuï¿\nñ;,⁄Ÿª≤`∫ ¥Óí7◊o©)QÇ§†y‡\nÂ™N2~Fj¬=çù}õ-êÏ‹Tû^˜gJ¿\"@J≤ÕOZúÿ\nj@iäÀ;Ï2+öA‹≤*¬ı≥≈{€;·Í¨≥C:Ñ °ïL\"k‡\0+ÄDÑµ=≥ü[Lƒÿ’≥±æ™∫dÀ\rÜdÃâå2oe≠`Ê–=äïåwj‘¨`2–q5ÂÎB´E≤\nbsR≤©=T:ñPlÿEƒ∏©~¡N•˚KfÉ∂YY…Y ¶i`¡{€-f∂bl€Ÿv≥oc|=t î¨g÷∞±ŸAÜLÜrVé+ˇŸø¥jM‚¬;Xˆ™íÅÄAY⁄¥“;[HÜ÷\rp∞Õ´-gπ£ˆçY‚m1∆±ë_©ÉTzâ9X”‹a …Ê¿ùñíwƒ…@+2‚¥…c^\". \n•f¬∆`{]íæÌ¶“cÿ¨⁄s¥`ÿ˝•Ì¢ÎO\0Ì3n^”¶¶ªJ2måÎÿf⁄Ç&»Œæ‘®[D⁄-¥£≤Ajù-®Xò÷é™tïü©”Uæ”5ÆåCjØùg%S˝ûóbc∂†Ÿ“⁄â©€1\nΩ√;“b⁄Y¶V~a1jä›ùP´¨v08`LñßÌñídw*|’Ûi‚ÀµßãÏEÏkÄVY¿ﬁ(ﬁ»¢06 á@!⁄M™kÕ¨êá†-\"⁄G¥í\0°¿(PSQ°W3Õs@0≈≠U~Á9\$ù#‘ÕÏA:;*YhÎî‡¢Ã*Ÿõ˜-n[…lsê»†‚ ñ!5 1™•¿…Ä.#æË–¿3÷ø¿«E´<¨J\\'0≈¬l•¬\"®4æñ¢ÿPÃïÂ0?\0001Äd\0^\nåúÍ¿ƒ¥Ég<\0\\êêTV@í¿ÖY&∞4°%ö;\"]mì…»\nœl™rÖ≤¬º∂ ˆ/\0kl 	Ω≤Îe!K¿[8ìMlº)x{g÷Õ-ò\08∂álÍŸ®P6—÷[@ùé\0¬⁄bÙ‡ΩáaÁc€Q6AT	)‘êñÃÌ§[Xú·mX7mµ‡ñÿ-ó€T∂omnŸÕ¥€e6’-û€h∂mÆÅı∂KnQmπ[S∂ïm÷⁄¿Koa‘€{°m˙É-∑â’ñ’m∑[D∑mhYmµÀmA{)\$@å∑m¶⁄ê˙I‚€€ê∂ØI!e∏[k¥í-±€ö∑nJòù∑äI€m«€o∑m∆‹ıπI—≠‘€ó∂Ôn∂€\r€jÏ-óœR∑nV›0˘ÍVÂ-ñ€üû•nf›-¥IÍVÁm‡€]û•mûﬁ-ª‡\$∂È-‹[¬∑Qo&‹åı+uñÙ-´œR∂ón“ŸISoVˆˆ[⁄∑onˆﬁç∑˚{ÄÁ7€Ñ∑©mÜﬂ-ªÀqV‰mÚ€¿∑õmvﬂ-ºK}ˆÁÌÚ€»∑Áo≤ﬁ]∑ã|∂–≠Ò€Â∑ßoﬁ⁄Ωæ[zˆ˘ÌóŒo∑∑pŸM¿k{ˆ˙Ó\0€‚∏≤‡5æÎ|gÊ[˙ùÂpZvE∑âƒˆ˘Æ\0[a∏7o™‹µ¡Îev¯Ó\r€Ú∏'mÜâù¿ªÑ6ÀËô‹ ∑sDŒ‡ï¿ÀÖ\0In\\2∏YnÜﬂ;yó	Óœ\0∂ÒDŒﬂ˝√Àe4LÓ‹∏op‚5ƒ9ÿ˜\nÆ‹∏í≤r≠∏á4Ó‹∏ßpf‚ΩøÀÅ‡Ó(‹A∏mq*åù≈S∞˜Æ&[D†1p∫„4Ú€Ü7\nÓ%Oí∏≈o⁄‚≈	ı7Æ-\\O∏π<∂‚ı∆¿Ωî2n2€]°ìq¢„ı¬;ãt2n‹z\0øC&„Ö≈˚èv˛mÒ–…∏Îq∆‰8[è6Óhd‹Eπ mB‚E…{âvﬁ.L\\Ä∑?rb„]…Käó#n+\\Éûqrrv=»˚ëW\$g\\®∏√rí„Àè∂Á¬ó‹π!r÷‰ıπñ∑(m¢\\µπnÊÂ≠»õê◊-n‹´πkr®:»Îk6Œ.e[_∂—s6‰-Ã€òW3nb‹…Yo>‹≠Ã€è73nK-œYcm>ÊeÕ{o∑3Óq\\ πïqJ‹}Œ{ö78Æi‹‚π´sûÊEŒ{õw8Æn‹÷πør∆ÁMÕzHw8n{‹ÊπıqÆÊU∆Îúó=Óv‹˜πﬂsÍÁçœ´ûw=Ó\"Ä#\0”9“tç0óC∫›úÈ8ñvLÛkôS´.~,ªú·p™Ë›œÎõÛìnéöi°˝tî“åÍ€•¿(Ç›+w<.Èp:7Lg8\\ıπï>*È§˘;ß7HÆèN.∫ssÆÊ¸Ù€ß7AÓ†›	∫Ét.ÈÌ”köÂL.öN:∫õtÍ]‘£”πÓ¶›D∫ßuÍù‘ã§”œ.öNo∫ªtÇÊºÊ´´∑T.oœd∫ªtËÙÍ{´∑V.ìN?∫ªu*Ê¸‚{¶ìùÆπ›<∫M9bÎù’+Æ˜Z.ª›V∫Ôu≤ÈL‚ªÆw\\.èNU∫i8ñÏ=◊k•3åÓ√›zªuÚÏ]◊Î±w`.ñNhªvÈ1µ¶ì•ÓÕ›ä∫Y:JÏ›ÿÎ≥˜d.œ›íª?vRÈ|ÏÀ≥wf\r(Ö/∫bíÎÀdkÓg\\˘ªel∆ËÀ∞\$ói\0›œ@ªiv¢qç∑K∑&ö@í]¨&\0†¶›ù€k∑W8ÓÂ]ƒúÈvæÁEÕïêj\nn—]Ωü„wBÌ5›k∏ ÓË]∆ª{s‚Óï€+üwpÓ÷Œeª_>æÔ=›Y∆3µÓÛ›Ÿª◊wnt›ﬁ{ª”åné]‰ªèGjÌ{sªø7Y.›P@ªÛvÚÔ•›π“w~n˘“≤ª_tÍÔµ€ŸıóÅÓ˛]ƒû´x{¿˜vÁ(ﬁºu2L„Á˜kÓß^ü„x6Ìl‚€√óÑg∆ﬁ∏Í\0å® ˙HtãÓ{]∫úãtVÒHÀ≈sÆ÷GOÒºOxƒ\ru\$:˜çgl^ºuzı˚µ˜XØ ﬁ ªèuöÚ\r·≠WênÌ›nºÉxÎï·ÎÆóîÆœ]Ω∫ÒyJÔT˙+µÛõØ-›€ªyJ,Ú[µ˜c/O‚º«xFrÊ;ªwfØ]úº€yVë˝‰k∑≥”.◊œ0ºÎw∫åù’´µ≥∆.ò€Tº˚<∂Û˝Á À∑n.ÊöQ;x\0Ï=ﬁ„∞˜p√ﬁz&\0vÔù\$;π7xV]“CªczVÓ}·õ«Wün\nﬁöªØzzÙT‚k”ó£Ä›“CΩ#wÇËùÈ[ºw°oG›ÊΩ5w¢ı}ﬁ©Ã∑¢'ﬁ}úÎz⁄ı eŸÃ6À üÜpsùk¢_]pK]kö÷6ΩäŒñ}t9è†Îl◊xÆ°[Ç\r”™Ël:≠WWC\0îOuê\rsXe0~·OA˛_Å]ˆ‹2∏s“WCáÉT\rÉHÎuÕXΩ\0Kk']ΩZ⁄Ô\0ÿ∂◊]Æ›V˘≥≠{XBu‹Ô}¡{©éææfˆ™Ôêàò\$◊x¥y_JºtLjÚL(´ö√YØ%[2º•t»7V‡ÿWôgöøΩ“Fˆ®ïI!√PØY]˝òú%\nvÒ	*TBZ.›W≈°):Zf◊ %ŸæYUV•ÔZæ7±°:VAfˇh*˘ùÏäÂŒCÃlóU\\ùÌÏ ÖU|og◊ÑÖ)\\“˙;{⁄µ|okìX™àMä©‘ƒöæ7∏aÀﬁﬁ`È\nhõ\$)ÀÍ∞Àor1kÆ_\nÜ{!∫®ó∫ò>’ÒΩ€{æ˚4 È@\n‡—ÄD∞Vö˘ï^Kﬁ5¢·Rµ±eùV∆•ùwcïp™Ü2^Çı0û˜˝5K‡7…a4Ww´…fÇ¯-¯p\n‹a\\÷∆´—W≤¯d-!ïE*ˆ_´…}.¸º-∂d€™¢XGÖ»…Çû—8ñr ≤^0ÉıZJµı]ÍÃ7 X˙÷p≤!Tf≥\rÏh`∑ÕóπX¢Ü|Í≤´+Vî◊œY\r÷y¨k}ˆmmJÁï1%˛WÒøã>∂s´È∑∂aí1Öò~˘ï≠ıK€∑◊:Vvá+{rd¿FJ5_Î0ﬁÁß«Ó˚!?*–⁄¨y√9≠ZÚÒDÓı¶oº±û¨ù\nùP ‘ø†æáæøÃN8[‡ó˙!UÁ≠ëYÜ¡øÊvî´e”Ù¿&`˘¶F«oË2øø0∂ú7g,u-KXÍáà!óÃ161’∞‡Ü6¬Œ°ÉjàÑ—,ø_Å∞CQŒÃ„E{‰\rò⁄ÜT¨≥ˇz£çL[˛ï3¶kﬂ{f≤¶•BRk— ƒ≥¿rÕ¬®dã!’Yn√≥™ES·'h<f’©€Vç \nt•ÈƒÚ`É)mlÖzäÃ%Q∆£j˜Æ⁄“Â\$=¿∫êziÛÄi/4í[	≈ä»Jjú2Zˆˇ.∫ÅíÉ£oÚ§<ìs6æE+Å_Öw‡ûHZ}ïŒéí0;å0¿ˆ˚‡è°-á∏ì…`mﬁüe¥∏Â…#©ã¡≤`àr˛®<íëjpÌŸ≠g6ñÇ HP&Ï&\0005‡àq<ê\\#~	¢Õ•£-‡àåª3#©≥¢ÿ\$„2∑_å…¢–`(À…‰åS§Ÿ\0≈'8§ñ ™.íQ`êpÿK«´Ãáj&0N§Ÿ~ÇÖ√a˜\$õ*\"˛®∞Rl0®∞€˛¨qôÚpü®¥ÓÌ„P7‡’íz‡\0¯`3™´»^∂`}¡∆%≠€∫y˝`xî:´\$ïPUS6˝x<FŸ`·®+–~=AVÚÄ\0=6ﬂÉ…’{ÖåŒ•ÅÖu_\"ÉÎrÔ…ˆ\0¬§l∫ÓÙÎN@(∏D\rp»1+∑Ñ@UF–7/Mbøª‚.\$§{ÆˇC0ë`ıL§6å\$.◊K›;]ã®É…m@gk™aÔ`≤¡PÓ	òw≈ M∫TÅ6Îsà€ªPåp6[‡^Åp\0/á8„-∞NåÎ\n»l&AE‚ö∫¸!\$¯G\n∞\$¶¸éø#π∏ATˆL¶…#ﬁ˛‡}vÈÖåèÛáåÅE„π·iÖä0;æ,,ÔØp∏F©,Y◊·¨@?ÿ^[¯H3Ü†√∏‡.†JúéH9√_\nìØÃ*TÛ}HFZp≈ˇf«ÓÀbaíÇjäQôÄ 0°⁄ü t~tX≤Ü∞‰xdë ƒßÜà;ÊhÆØå06EØ”Ül™∫ÂgóA¿Ë-è˚ÜC\rä◊5©∆‹Oa¥√d∫‡`Ê'6∏k∆·µ\\É™	x„…8-FÕVpÜ‡abóÿe@¬…™oÜQ›0E∞f\"B¿;Å*nAám–*p,;¯u@◊a€6Røqd∞å·ÍSL.l=cf˘,ò®ÎÖ»ƒQf±é„!®-ågá˙2Ñdò…‚Õ£&Sw;`iN`Êÿv‚Ä‚\0Ô\"µRñ ‡Ü¿vÈÃa’¿Ï!6eHQOO0Í·>¯!›æ4Xábä˚¬+`∏{àéú;uwbòR‡»öT;Òﬂé¶‰ÄCPÚV#ä4)]\rÈ¡7∏”l∞càdbÅ|Bˆ>óƒÖàŒŸKüÏHxçùbƒèà˝\rı„ä@¨«⁄ƒól¶BË%,8íˆbYäºØI⁄Ê%K“çƒÈ3ƒ†€	ˆaÊˆ‰é/q!∏Ωq\rÉh2^&Ë≈Œ≈ãbt\0⁄ÏQ[+Í±8√äp»ç|ƒÚYœN'ë\0¿ôA6aaƒ¸≤,x%¿\$8hAŒ‚y∑Åıq¨nRûAM@·ƒ∞“Ò¨RÙÒqC‚\"\nì}&àúbàwäÚÌÕtptv∫iæb≤@≈êé|¶ß<W¨B°f‚Ω`‘≈?%'tWÃ/Ì®∞©≈d≈mä—˝ìõLObˇq`H§ƒ5Åúœ˛#ÏËﬁ]ÒF\r.Ë˛+é*∆¯oBÒ8EÅ≈®›£>W¡—~1Oø¡äŸ+<¨#≤≥¿‚§—\0ÙÓ0ƒÿ3ÿ∫ÉÅ`X\r@Òåﬁ00∏]±yú≈‘pD¶√´`apR‚¸å[ÇÃƒ+uP Ã@;:˚-Œ≥À\0f8•e—cãlUπv0·e6\0‹b‹µ±å=˛Æ¨b¯ƒ÷”á4ù‡—Ö≠†(•\$0yöGÁÇ˚0<ºd@õ„+#ËAKîo∞#.H1çúƒjÎëP‹g/QëGßñS5%VË“éb≠tÖüñ±\0N◊ÇÆTLJ‹±°¡∆â8Jÿp¡pEm∞ÅÑrº§5`9ˆMÅ∂C\0_∞™'®\$™¿#∏ñEÅ!\\êüa¿)ï‚s†ò°€êÇÁ“1∏ã\0¶ñóõrÏn§œq¶Äe∆’àû7∞\nØNt#Yf`ò°ô‰¡@Fqø≠Ì∆˜L≠z4‘ºo∏—%ı®ıê‡⁄[⁄Z¢òƒé\"∆g·∆ãq‘MIVÈùøÎ¶f)<]¢ß∆o!' ó@Vòœ¶ˆ\0");}elseif($_GET["file"]=="logo.png"){header("Content-Type: image/png");echo"âPNG\r\n\n\0\0\0\rIHDR\0\0\09\0\0\09\0\0\0~6û∂\0\0\0000PLTE\0\0\0Éó≠+NvYtìsâ£ûÆæ¥æÃ»“⁄¸çë¸su¸IJ˜”‘¸/.¸¸Ø±˙¸˙C•◊\0\0\0tRNS\0@Êÿf\0\0\0	pHYs\0\0\0\0\0öú\0\0¥IDAT8ç’îÕN¬@«˚E·Ïlœ∂ı§p6àG.\$=£•«>Å·	w5r}Çz7≤>ÄëPÂ#\$å≥K°j´7ç¸›∂øÃŒÃ?4mïÑà—˜t&Ó~¿3!0ì0äö^ÑΩAf0ﬁ\"ÂΩÌ, *†Á4ºå‚o•EË≥Ë◊X(*Y”Ûº∏	6	ÔPcOW¢…Œ‹ämí¨rÉ0√~/†·L®\rXj#÷m ¡˙j¿CÄ]G¶mÊ\0∂}ﬁÀ¨ﬂëuºA9¿X£\n‘ÿ8ºV±Yƒ+«D#®iqﬁnKQ8J‡1Q6≤ÊY0ß`ïüP≥bQç\\hî~>Û:pS…Ä£¶º¢ÿÛGEıQ=ÓIœ{í*ü3Î2£7˜\ne LËBä~–/R(\$∞) Áã ó¡HQnÄiï6J∂	<ù◊-.ñw«…™jÍVm´Í¸mø?SﬁH†õv√Ã˚Ò∆©ß›\0‡÷^’q´∂)™ó€]˜ãUπ92—,;ˇ«çÓ'p¯µ£!XÀÉ‰⁄‹ˇLÒD.ªt√¶ó˝/w√”‰ÏR˜ù	w≠d”÷r2Ô∆§™4[=ΩE5˜S+Òóc\0\0\0\0IENDÆB`Ç";}exit;}if($_GET["script"]=="version"){$o=get_temp_dir()."/adminer.version";@unlink($o);$q=file_open_lock($o);if($q)file_write_unlock($q,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];define('Adminer\HTTPS',($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure"));@ini_set("session.use_trans_sid",'0');if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");session_set_cookie_params(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",HTTPS,true);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$ad);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("precision",'15');function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=$u;return
call_user_func_array('Adminer\lang_format',$ua);}function
lang_format($dj,$Jf=null){if(is_array($dj)){$Ng=($Jf==1?0:1);$dj=$dj[$Ng];}$dj=str_replace("'",'‚Äô',$dj);$ua=func_get_args();array_shift($ua);$md=str_replace("%d","%s",$dj);if($md!=$dj)$ua[0]=format_number($Jf);return
vsprintf($md,$ua);}define('Adminer\LANG','en');abstract
class
SqlDb{static$instance;var$extension;var$flavor='';var$server_info;var$affected_rows=0;var$info='';var$errno=0;var$error='';protected$multi;abstract
function
attach($N,$V,$F);abstract
function
quote($Q);abstract
function
select_db($Nb);abstract
function
query($H,$oj=false);function
multi_query($H){return$this->multi=$this->query($H);}function
store_result(){return$this->multi;}function
next_result(){return
false;}}if(extension_loaded('pdo')){abstract
class
PdoDb
extends
SqlDb{protected$pdo;function
dsn($nc,$V,$F,array$bg=array()){$bg[\PDO::ATTR_ERRMODE]=\PDO::ERRMODE_SILENT;$bg[\PDO::ATTR_STATEMENT_CLASS]=array('Adminer\PdoResult');try{$this->pdo=new
\PDO($nc,$V,$F,$bg);}catch(\Exception$Hc){return$Hc->getMessage();}$this->server_info=@$this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);return'';}function
quote($Q){return$this->pdo->quote($Q);}function
query($H,$oj=false){$I=$this->pdo->query($H);$this->error="";if(!$I){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($I);return$I;}function
store_result($I=null){if(!$I){$I=$this->multi;if(!$I)return
false;}if($I->columnCount()){$I->num_rows=$I->rowCount();return$I;}$this->affected_rows=$I->rowCount();return
true;}function
next_result(){$I=$this->multi;if(!is_object($I))return
false;$I->_offset=0;return@$I->nextRowset();}}class
PdoResult
extends
\PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch_array(\PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch_array(\PDO::FETCH_NUM);}private
function
fetch_array($tf){$J=$this->fetch($tf);return($J?array_map(array($this,'unresource'),$J):$J);}private
function
unresource($X){return(is_resource($X)?stream_get_contents($X):$X);}function
fetch_field(){$K=(object)$this->getColumnMeta($this->_offset++);$U=$K->pdo_type;$K->type=($U==\PDO::PARAM_INT?0:15);$K->charsetnr=($U==\PDO::PARAM_LOB||(isset($K->flags)&&in_array("blob",(array)$K->flags))?63:0);return$K;}function
seek($C){for($s=0;$s<$C;$s++)$this->fetch();}}}function
add_driver($t,$B){SqlDriver::$drivers[$t]=$B;}function
get_driver($t){return
SqlDriver::$drivers[$t];}abstract
class
SqlDriver{static$instance;static$drivers=array();static$extensions=array();static$jush;protected$conn;protected$types=array();var$insertFunctions=array();var$editFunctions=array();var$unsigned=array();var$operators=array();var$functions=array();var$grouping=array();var$onActions="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";var$partitionBy=array();var$inout="IN|OUT|INOUT";var$enumLength="'(?:''|[^'\\\\]|\\\\.)*'";var$generated=array();static
function
connect($N,$V,$F){$f=new
Db;return($f->attach($N,$V,$F)?:$f);}function
__construct(Db$f){$this->conn=$f;}function
types(){return
call_user_func_array('array_merge',array_values($this->types));}function
structuredTypes(){return
array_map('array_keys',$this->types);}function
enumLength(array$m){}function
unconvertFunction(array$m){}function
select($R,array$M,array$Z,array$wd,array$dg=array(),$z=1,$D=0,$Wg=false){$ue=(count($wd)<count($M));$H=adminer()->selectQueryBuild($M,$Z,$wd,$dg,$z,$D);if(!$H)$H="SELECT".limit(($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$M)."\nFROM ".table($R),($Z?"\nWHERE ".implode(" AND ",$Z):"").($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):""),$z,($D?$z*$D:0),"\n");$oi=microtime(true);$J=$this->conn->query($H);if($Wg)echo
adminer()->selectQuery($H,$oi,!$J);return$J;}function
delete($R,$fh,$z=0){$H="FROM ".table($R);return
queries("DELETE".($z?limit1($R,$H,$fh):" $H$fh"));}function
update($R,array$O,$fh,$z=0,$Rh="\n"){$Ij=array();foreach($O
as$x=>$X)$Ij[]="$x = $X";$H=table($R)." SET$Rh".implode(",$Rh",$Ij);return
queries("UPDATE".($z?limit1($R,$H,$fh,$Rh):" $H$fh"));}function
insert($R,array$O){return
queries("INSERT INTO ".table($R).($O?" (".implode(", ",array_keys($O)).")\nVALUES (".implode(", ",$O).")":" DEFAULT VALUES").$this->insertReturning($R));}function
insertReturning($R){return"";}function
insertUpdate($R,array$L,array$G){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($H,$Qi){}function
convertSearch($u,array$X,array$m){return$u;}function
value($X,array$m){return(method_exists($this->conn,'value')?$this->conn->value($X,$m):$X);}function
quoteBinary($Dh){return
q($Dh);}function
warnings(){}function
tableHelp($B,$ye=false){}function
inheritsFrom($R){return
array();}function
inheritedTables($R){return
array();}function
partitionsInfo($R){return
array();}function
hasCStyleEscapes(){return
false;}function
engines(){return
array();}function
supportsIndex(array$S){return!is_view($S);}function
indexAlgorithms(array$yi){return
array();}function
checkConstraints($R){return
get_key_vals("SELECT c.CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS c
JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS t ON c.CONSTRAINT_SCHEMA = t.CONSTRAINT_SCHEMA AND c.CONSTRAINT_NAME = t.CONSTRAINT_NAME
WHERE c.CONSTRAINT_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
AND t.TABLE_NAME = ".q($R)."
AND CHECK_CLAUSE NOT LIKE '% IS NOT NULL'",$this->conn);}function
allFields(){$J=array();if(DB!=""){foreach(get_rows("SELECT TABLE_NAME AS tab, COLUMN_NAME AS field, IS_NULLABLE AS nullable, DATA_TYPE AS type, CHARACTER_MAXIMUM_LENGTH AS length".(JUSH=='sql'?", COLUMN_KEY = 'PRI' AS `primary`":"")."
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = ".q($_GET["ns"]!=""?$_GET["ns"]:DB)."
ORDER BY TABLE_NAME, ORDINAL_POSITION",$this->conn)as$K){$K["null"]=($K["nullable"]=="YES");$J[$K["tab"]][]=$K;}}return$J;}}add_driver("sqlite","SQLite");if(isset($_GET["sqlite"])){define('Adminer\DRIVER',"sqlite");if(class_exists("SQLite3")&&$_GET["ext"]!="pdo"){abstract
class
SqliteDb
extends
SqlDb{var$extension="SQLite3";private$link;function
attach($o,$V,$F){$this->link=new
\SQLite3($o);$Lj=$this->link->version();$this->server_info=$Lj["versionString"];return'';}function
query($H,$oj=false){$I=@$this->link->query($H);$this->error="";if(!$I){$this->errno=$this->link->lastErrorCode();$this->error=$this->link->lastErrorMsg();return
false;}elseif($I->numColumns())return
new
Result($I);$this->affected_rows=$this->link->changes();return
true;}function
quote($Q){return(is_utf8($Q)?"'".$this->link->escapeString($Q)."'":"x'".first(unpack('H*',$Q))."'");}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;}function
fetch_assoc(){return$this->result->fetchArray(SQLITE3_ASSOC);}function
fetch_row(){return$this->result->fetchArray(SQLITE3_NUM);}function
fetch_field(){$d=$this->offset++;$U=$this->result->columnType($d);return(object)array("name"=>$this->result->columnName($d),"type"=>($U==SQLITE3_TEXT?15:0),"charsetnr"=>($U==SQLITE3_BLOB?63:0),);}function
__destruct(){$this->result->finalize();}}}elseif(extension_loaded("pdo_sqlite")){abstract
class
SqliteDb
extends
PdoDb{var$extension="PDO_SQLite";function
attach($o,$V,$F){return$this->dsn(DRIVER.":$o","","");}}}if(class_exists('Adminer\SqliteDb')){class
Db
extends
SqliteDb{function
attach($o,$V,$F){parent::attach($o,$V,$F);$this->query("PRAGMA foreign_keys = 1");$this->query("PRAGMA busy_timeout = 500");return'';}function
select_db($o){if(is_readable($o)&&$this->query("ATTACH ".$this->quote(preg_match("~(^[/\\\\]|:)~",$o)?$o:dirname($_SERVER["SCRIPT_FILENAME"])."/$o")." AS a"))return!self::attach($o,'','');return
false;}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLite3","PDO_SQLite");static$jush="sqlite";protected$types=array(array("integer"=>0,"real"=>0,"numeric"=>0,"text"=>0,"blob"=>0));var$insertFunctions=array();var$editFunctions=array("integer|real|numeric"=>"+/-","text"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("hex","length","lower","round","unixepoch","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){if($F!="")return'Database does not support password.';return
parent::connect(":memory:","","");}function
__construct(Db$f){parent::__construct($f);if(min_version(3.31,0,$f))$this->generated=array("STORED","VIRTUAL");}function
structuredTypes(){return
array_keys($this->types[0]);}function
insertUpdate($R,array$L,array$G){$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";return
queries("REPLACE INTO ".table($R)." (".implode(", ",array_keys(reset($L))).") VALUES\n".implode(",\n",$Ij));}function
tableHelp($B,$ye=false){if($B=="sqlite_sequence")return"fileformat2.html#seqtab";if($B=="sqlite_master")return"fileformat2.html#$B";}function
checkConstraints($R){preg_match_all('~ CHECK *(\( *(((?>[^()]*[^() ])|(?1))*) *\))~',get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$this->conn),$Ze);return
array_combine($Ze[2],$Ze[2]);}function
allFields(){$J=array();foreach(tables_list()as$R=>$U){foreach(fields($R)as$m)$J[$R][]=$m;}return$J;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
array();}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)||get_val("SELECT sqlite_compileoption_used('ENABLE_UPDATE_DELETE_LIMIT')")?limit($H,$Z,1,0,$Rh):" $H WHERE rowid = (SELECT rowid FROM ".table($R).$Z.$Rh."LIMIT 1)");}function
db_collation($j,$jb){return
get_val("PRAGMA encoding");}function
logged_user(){return
get_current_user();}function
tables_list(){return
get_key_vals("SELECT name, type FROM sqlite_master WHERE type IN ('table', 'view') ORDER BY (name = 'sqlite_sequence'), name");}function
count_tables($i){return
array();}function
table_status($B=""){$J=array();foreach(get_rows("SELECT name AS Name, type AS Engine, 'rowid' AS Oid, '' AS Auto_increment FROM sqlite_master WHERE type IN ('table', 'view') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K){$K["Rows"]=get_val("SELECT COUNT(*) FROM ".idf_escape($K["Name"]));$J[$K["Name"]]=$K;}foreach(get_rows("SELECT * FROM sqlite_sequence".($B!=""?" WHERE name = ".q($B):""),null,"")as$K)$J[$K["name"]]["Auto_increment"]=$K["seq"];return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return!get_val("SELECT sqlite_compileoption_used('OMIT_FOREIGN_KEY')");}function
fields($R){$J=array();$G="";foreach(get_rows("PRAGMA table_".(min_version(3.31)?"x":"")."info(".table($R).")")as$K){$B=$K["name"];$U=strtolower($K["type"]);$k=$K["dflt_value"];$J[$B]=array("field"=>$B,"type"=>(preg_match('~int~i',$U)?"integer":(preg_match('~char|clob|text~i',$U)?"text":(preg_match('~blob~i',$U)?"blob":(preg_match('~real|floa|doub~i',$U)?"real":"numeric")))),"full_type"=>$U,"default"=>(preg_match("~^'(.*)'$~",$k,$A)?str_replace("''","'",$A[1]):($k=="NULL"?null:$k)),"null"=>!$K["notnull"],"privileges"=>array("select"=>1,"insert"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["pk"],);if($K["pk"]){if($G!="")$J[$G]["auto_increment"]=false;elseif(preg_match('~^integer$~i',$U))$J[$B]["auto_increment"]=true;$G=$B;}}$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R));$u='(("[^"]*+")+|[a-z0-9_]+)';preg_match_all('~'.$u.'\s+text\s+COLLATE\s+(\'[^\']+\'|\S+)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));if($J[$B])$J[$B]["collation"]=trim($A[3],"'");}preg_match_all('~'.$u.'\s.*GENERATED ALWAYS AS \((.+)\) (STORED|VIRTUAL)~i',$ii,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$B=str_replace('""','"',preg_replace('~^"|"$~','',$A[1]));$J[$B]["default"]=$A[3];$J[$B]["generated"]=strtoupper($A[4]);}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$ii=get_val("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ".q($R),0,$g);if(preg_match('~\bPRIMARY\s+KEY\s*\((([^)"]+|"[^"]*"|`[^`]*`)++)~i',$ii,$A)){$J[""]=array("type"=>"PRIMARY","columns"=>array(),"lengths"=>array(),"descs"=>array());preg_match_all('~((("[^"]*+")+|(?:`[^`]*+`)+)|(\S+))(\s+(ASC|DESC))?(,\s*|$)~i',$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$A){$J[""]["columns"][]=idf_unescape($A[2]).$A[4];$J[""]["descs"][]=(preg_match('~DESC~i',$A[5])?'1':null);}}if(!$J){foreach(fields($R)as$B=>$m){if($m["primary"])$J[""]=array("type"=>"PRIMARY","columns"=>array($B),"lengths"=>array(),"descs"=>array(null));}}$mi=get_key_vals("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ".q($R),$g);foreach(get_rows("PRAGMA index_list(".table($R).")",$g)as$K){$B=$K["name"];$v=array("type"=>($K["unique"]?"UNIQUE":"INDEX"));$v["lengths"]=array();$v["descs"]=array();foreach(get_rows("PRAGMA index_info(".idf_escape($B).")",$g)as$Ch){$v["columns"][]=$Ch["name"];$v["descs"][]=null;}if(preg_match('~^CREATE( UNIQUE)? INDEX '.preg_quote(idf_escape($B).' ON '.idf_escape($R),'~').' \((.*)\)$~i',$mi[$B],$qh)){preg_match_all('/("[^"]*+")+( DESC)?/',$qh[2],$Ze);foreach($Ze[2]as$x=>$X){if($X)$v["descs"][$x]='1';}}if(!$J[""]||$v["type"]!="UNIQUE"||$v["columns"]!=$J[""]["columns"]||$v["descs"]!=$J[""]["descs"]||!preg_match("~^sqlite_~",$B))$J[$B]=$v;}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("PRAGMA foreign_key_list(".table($R).")")as$K){$p=&$J[$K["id"]];if(!$p)$p=$K;$p["source"][]=$K["from"];$p["target"][]=$K["to"];}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`"[]+|`[^`]*`|"[^"]*")* AS\s+~iU','',get_val("SELECT sql FROM sqlite_master WHERE type = 'view' AND name = ".q($B))));}function
collations(){return(isset($_GET["create"])?get_vals("PRAGMA collation_list",1):array());}function
information_schema($j){return
false;}function
error(){return
h(connection()->error);}function
check_sqlite_name($B){$Pc="db|sdb|sqlite";if(!preg_match("~^[^\\0]*\\.($Pc)\$~",$B)){connection()->error=sprintf('Please use one of the extensions %s.',str_replace("|",", ",$Pc));return
false;}return
true;}function
create_database($j,$c){if(file_exists($j)){connection()->error='File exists.';return
false;}if(!check_sqlite_name($j))return
false;try{$_=new
Db();$_->attach($j,'','');}catch(\Exception$Hc){connection()->error=$Hc->getMessage();return
false;}$_->query('PRAGMA encoding = "UTF-8"');$_->query('CREATE TABLE adminer (i)');$_->query('DROP TABLE adminer');return
true;}function
drop_databases($i){connection()->attach(":memory:",'','');foreach($i
as$j){if(!@unlink($j)){connection()->error='File exists.';return
false;}}return
true;}function
rename_database($B,$c){if(!check_sqlite_name($B))return
false;connection()->attach(":memory:",'','');connection()->error='File exists.';return@rename(DB,$B);}function
auto_increment(){return" PRIMARY KEY AUTOINCREMENT";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$Bj=($R==""||$jd);foreach($n
as$m){if($m[0]!=""||!$m[1]||$m[2]){$Bj=true;break;}}$b=array();$og=array();foreach($n
as$m){if($m[1]){$b[]=($Bj?$m[1]:"ADD ".implode($m[1]));if($m[0]!="")$og[$m[0]]=$m[1][0];}}if(!$Bj){foreach($b
as$X){if(!queries("ALTER TABLE ".table($R)." $X"))return
false;}if($R!=$B&&!queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)))return
false;}elseif(!recreate_table($R,$B,$b,$og,$jd,$_a))return
false;if($_a){queries("BEGIN");queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));if(!connection()->affected_rows)queries("INSERT INTO sqlite_sequence (name, seq) VALUES (".q($B).", $_a)");queries("COMMIT");}return
true;}function
recreate_table($R,$B,array$n,array$og,array$jd,$_a="",$w=array(),$jc="",$ja=""){if($R!=""){if(!$n){foreach(fields($R)as$x=>$m){if($w)$m["auto_increment"]=0;$n[]=process_field($m,$m);$og[$x]=idf_escape($x);}}$Vg=false;foreach($n
as$m){if($m[6])$Vg=true;}$lc=array();foreach($w
as$x=>$X){if($X[2]=="DROP"){$lc[$X[1]]=true;unset($w[$x]);}}foreach(indexes($R)as$Be=>$v){$e=array();foreach($v["columns"]as$x=>$d){if(!$og[$d])continue
2;$e[]=$og[$d].($v["descs"][$x]?" DESC":"");}if(!$lc[$Be]){if($v["type"]!="PRIMARY"||!$Vg)$w[]=array($v["type"],$Be,$e);}}foreach($w
as$x=>$X){if($X[0]=="PRIMARY"){unset($w[$x]);$jd[]="  PRIMARY KEY (".implode(", ",$X[2]).")";}}foreach(foreign_keys($R)as$Be=>$p){foreach($p["source"]as$x=>$d){if(!$og[$d])continue
2;$p["source"][$x]=idf_unescape($og[$d]);}if(!isset($jd[" $Be"]))$jd[]=" ".format_foreign_key($p);}queries("BEGIN");}$Ua=array();foreach($n
as$m){if(preg_match('~GENERATED~',$m[3]))unset($og[array_search($m[0],$og)]);$Ua[]="  ".implode($m);}$Ua=array_merge($Ua,array_filter($jd));foreach(driver()->checkConstraints($R)as$Wa){if($Wa!=$jc)$Ua[]="  CHECK ($Wa)";}if($ja)$Ua[]="  CHECK ($ja)";$Ki=($R==$B?"adminer_$B":$B);if(!queries("CREATE TABLE ".table($Ki)." (\n".implode(",\n",$Ua)."\n)"))return
false;if($R!=""){if($og&&!queries("INSERT INTO ".table($Ki)." (".implode(", ",$og).") SELECT ".implode(", ",array_map('Adminer\idf_escape',array_keys($og)))." FROM ".table($R)))return
false;$kj=array();foreach(triggers($R)as$ij=>$Ri){$hj=trigger($ij,$R);$kj[]="CREATE TRIGGER ".idf_escape($ij)." ".implode(" ",$Ri)." ON ".table($B)."\n$hj[Statement]";}$_a=$_a?"":get_val("SELECT seq FROM sqlite_sequence WHERE name = ".q($R));if(!queries("DROP TABLE ".table($R))||($R==$B&&!queries("ALTER TABLE ".table($Ki)." RENAME TO ".table($B)))||!alter_indexes($B,$w))return
false;if($_a)queries("UPDATE sqlite_sequence SET seq = $_a WHERE name = ".q($B));foreach($kj
as$hj){if(!queries($hj))return
false;}queries("COMMIT");}return
true;}function
index_sql($R,$U,$B,$e){return"CREATE $U ".($U!="INDEX"?"INDEX ":"").idf_escape($B!=""?$B:uniqid($R."_"))." ON ".table($R)." $e";}function
alter_indexes($R,$b){foreach($b
as$G){if($G[0]=="PRIMARY")return
recreate_table($R,$R,array(),array(),array(),"",$b);}foreach(array_reverse($b)as$X){if(!queries($X[2]=="DROP"?"DROP INDEX ".idf_escape($X[1]):index_sql($R,$X[0],$X[1],"(".implode(", ",$X[2]).")")))return
false;}return
true;}function
truncate_tables($T){return
apply_queries("DELETE FROM",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
move_tables($T,$Nj,$Ii){return
false;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"BEGIN\n\t;\nEND");$u='(?:[^`"\s]+|`[^`]*`|"[^"]*")+';$jj=trigger_options();preg_match("~^CREATE\\s+TRIGGER\\s*$u\\s*(".implode("|",$jj["Timing"]).")\\s+([a-z]+)(?:\\s+OF\\s+($u))?\\s+ON\\s*$u\\s*(?:FOR\\s+EACH\\s+ROW\\s)?(.*)~is",get_val("SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ".q($B)),$A);$Lf=$A[3];return
array("Timing"=>strtoupper($A[1]),"Event"=>strtoupper($A[2]).($Lf?" OF":""),"Of"=>idf_unescape($Lf),"Trigger"=>$B,"Statement"=>$A[4],);}function
triggers($R){$J=array();$jj=trigger_options();foreach(get_rows("SELECT * FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R))as$K){preg_match('~^CREATE\s+TRIGGER\s*(?:[^`"\s]+|`[^`]*`|"[^"]*")+\s*('.implode("|",$jj["Timing"]).')\s*(.*?)\s+ON\b~i',$K["sql"],$A);$J[$K["name"]]=array($A[1],$A[2]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
begin(){return
queries("BEGIN");}function
last_id($I){return
get_val("SELECT LAST_INSERT_ROWID()");}function
explain($f,$H){return$f->query("EXPLAIN QUERY PLAN $H");}function
found_rows($S,$Z){}function
types(){return
array();}function
create_sql($R,$_a,$si){$J=get_val("SELECT sql FROM sqlite_master WHERE type IN ('table', 'view') AND name = ".q($R));foreach(indexes($R)as$B=>$v){if($B=='')continue;$J
.=";\n\n".index_sql($R,$v['type'],$B,"(".implode(", ",array_map('Adminer\idf_escape',$v['columns'])).")");}return$J;}function
truncate_sql($R){return"DELETE FROM ".table($R);}function
use_sql($Nb,$si=""){}function
trigger_sql($R){return
implode(get_vals("SELECT sql || ';;\n' FROM sqlite_master WHERE type = 'trigger' AND tbl_name = ".q($R)));}function
show_variables(){$J=array();foreach(get_rows("PRAGMA pragma_list")as$K){$B=$K["name"];if($B!="pragma_list"&&$B!="compile_options"){$J[$B]=array($B,'');foreach(get_rows("PRAGMA $B")as$K)$J[$B][1].=implode(", ",$K)."\n";}}return$J;}function
show_status(){$J=array();foreach(get_vals("PRAGMA compile_options")as$ag)$J[]=explode("=",$ag,2)+array('','');return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|database|drop_col|dump|indexes|descidx|move_col|sql|status|table|trigger|variables|view|view_trigger)$~',$Uc);}}add_driver("pgsql","PostgreSQL");if(isset($_GET["pgsql"])){define('Adminer\DRIVER',"pgsql");if(extension_loaded("pgsql")&&$_GET["ext"]!="pdo"){class
PgsqlDb
extends
SqlDb{var$extension="PgSQL";var$timeout=0;private$link,$string,$database=true;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$j=adminer()->database();set_error_handler(array($this,'_error'));list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$this->string="host='$Md'".($Mg?" port='$Mg'":"")." user='".addcslashes($V,"'\\")."' password='".addcslashes($F,"'\\")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$this->string
.=" sslmode='".$ni["mode"]."'";$this->link=@pg_connect("$this->string dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'",PGSQL_CONNECT_FORCE_NEW);if(!$this->link&&$j!=""){$this->database=false;$this->link=@pg_connect("$this->string dbname='postgres'",PGSQL_CONNECT_FORCE_NEW);}restore_error_handler();if($this->link)pg_set_client_encoding($this->link,"UTF8");return($this->link?'':$this->error);}function
quote($Q){return(function_exists('pg_escape_literal')?pg_escape_literal($this->link,$Q):"'".pg_escape_string($this->link,$Q)."'");}function
value($X,array$m){return($m["type"]=="bytea"&&$X!==null?pg_unescape_bytea($X):$X);}function
select_db($Nb){if($Nb==adminer()->database())return$this->database;$J=@pg_connect("$this->string dbname='".addcslashes($Nb,"'\\")."'",PGSQL_CONNECT_FORCE_NEW);if($J)$this->link=$J;return$J;}function
close(){$this->link=@pg_connect("$this->string dbname='postgres'");}function
query($H,$oj=false){$I=@pg_query($this->link,$H);$this->error="";if(!$I){$this->error=pg_last_error($this->link);$J=false;}elseif(!pg_num_fields($I)){$this->affected_rows=pg_affected_rows($I);$J=true;}else$J=new
Result($I);if($this->timeout){$this->timeout=0;$this->query("RESET statement_timeout");}return$J;}function
warnings(){return
h(pg_last_notice($this->link));}function
copyFrom($R,array$L){$this->error='';set_error_handler(function($Cc,$l){$this->error=(ini_bool('html_errors')?html_entity_decode($l):$l);return
true;});$J=pg_copy_from($this->link,$R,$L);restore_error_handler();return$J;}}class
Result{var$num_rows;private$result,$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=pg_num_rows($I);}function
fetch_assoc(){return
pg_fetch_assoc($this->result);}function
fetch_row(){return
pg_fetch_row($this->result);}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->orgtable=pg_field_table($this->result,$d);$J->name=pg_field_name($this->result,$d);$U=pg_field_type($this->result,$d);$J->type=(preg_match(number_type(),$U)?0:15);$J->charsetnr=($U=="bytea"?63:0);return$J;}function
__destruct(){pg_free_result($this->result);}}}elseif(extension_loaded("pdo_pgsql")){class
PgsqlDb
extends
PdoDb{var$extension="PDO_PgSQL";var$timeout=0;function
attach($N,$V,$F){$j=adminer()->database();list($Md,$Mg)=host_port(addcslashes($N,"'\\"));$nc="pgsql:host='$Md'".($Mg?" port='$Mg'":"")." client_encoding=utf8 dbname='".($j!=""?addcslashes($j,"'\\"):"postgres")."'";$ni=adminer()->connectSsl();if(isset($ni["mode"]))$nc
.=" sslmode='".$ni["mode"]."'";return$this->dsn($nc,$V,$F);}function
select_db($Nb){return(adminer()->database()==$Nb);}function
query($H,$oj=false){$J=parent::query($H,$oj);if($this->timeout){$this->timeout=0;parent::query("RESET statement_timeout");}return$J;}function
warnings(){}function
copyFrom($R,array$L){$J=$this->pdo->pgsqlCopyFromArray($R,$L);$this->error=idx($this->pdo->errorInfo(),2)?:'';return$J;}function
close(){}}}if(class_exists('Adminer\PgsqlDb')){class
Db
extends
PgsqlDb{function
multi_query($H){if(preg_match('~\bCOPY\s+(.+?)\s+FROM\s+stdin;\n?(.*)\n\\\\\.$~is',str_replace("\r\n","\n",$H),$A)){$L=explode("\n",$A[2]);$this->affected_rows=count($L);return$this->copyFrom($A[1],$L);}return
parent::multi_query($H);}}}class
Driver
extends
SqlDriver{static$extensions=array("PgSQL","PDO_PgSQL");static$jush="pgsql";var$operators=array("=","<",">","<=",">=","!=","~","!~","LIKE","LIKE %%","ILIKE","ILIKE %%","IN","IS NULL","NOT LIKE","NOT ILIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","lower","round","to_hex","to_timestamp","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$nsOid="(SELECT oid FROM pg_namespace WHERE nspname = current_schema())";static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f))return$f;$Lj=get_val("SELECT version()",0,$f);$f->flavor=(preg_match('~CockroachDB~',$Lj)?'cockroach':'');$f->server_info=preg_replace('~^\D*([\d.]+[-\w]*).*~','\1',$Lj);if(min_version(9,0,$f))$f->query("SET application_name = 'Adminer'");if($f->flavor=='cockroach')add_driver(DRIVER,"CockroachDB");return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("smallint"=>5,"integer"=>10,"bigint"=>19,"boolean"=>1,"numeric"=>0,"real"=>7,"double precision"=>16,"money"=>20),'Date and time'=>array("date"=>13,"time"=>17,"timestamp"=>20,"timestamptz"=>21,"interval"=>0),'Strings'=>array("character"=>0,"character varying"=>0,"text"=>0,"tsquery"=>0,"tsvector"=>0,"uuid"=>0,"xml"=>0),'Binary'=>array("bit"=>0,"bit varying"=>0,"bytea"=>0),'Network'=>array("cidr"=>43,"inet"=>43,"macaddr"=>17,"macaddr8"=>23,"txid_snapshot"=>0),'Geometry'=>array("box"=>0,"circle"=>0,"line"=>0,"lseg"=>0,"path"=>0,"point"=>0,"polygon"=>0),);if(min_version(9.2,0,$f)){$this->types['Strings']["json"]=4294967295;if(min_version(9.4,0,$f))$this->types['Strings']["jsonb"]=4294967295;}$this->insertFunctions=array("char"=>"md5","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date|time"=>"+ interval/- interval","char|text"=>"||",);if(min_version(12,0,$f))$this->generated=array("STORED");$this->partitionBy=array("RANGE","LIST");if(!$f->flavor)$this->partitionBy[]="HASH";}function
enumLength(array$m){$zc=$this->types['User types'][$m["type"]];return($zc?type_values($zc):"");}function
setUserTypes($nj){$this->types['User types']=array_flip($nj);}function
insertReturning($R){$_a=array_filter(fields($R),function($m){return$m['auto_increment'];});return(count($_a)==1?" RETURNING ".idf_escape(key($_a)):"");}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
slowQuery($H,$Qi){$this->conn->query("SET statement_timeout = ".(1000*$Qi));$this->conn->timeout=1000*$Qi;return$H;}function
convertSearch($u,array$X,array$m){$Ni="char|text";if(strpos($X["op"],"LIKE")===false)$Ni
.="|date|time(stamp)?|boolean|uuid|inet|cidr|macaddr|".number_type();return(preg_match("~$Ni~",$m["type"])?$u:"CAST($u AS text)");}function
quoteBinary($Dh){return"'\\x".bin2hex($Dh)."'";}function
warnings(){return$this->conn->warnings();}function
tableHelp($B,$ye=false){$Re=array("information_schema"=>"infoschema","pg_catalog"=>($ye?"view":"catalog"),);$_=$Re[$_GET["ns"]];if($_)return"$_-".str_replace("_","-",$B).".html";}function
inheritsFrom($R){return
get_vals("SELECT relname FROM pg_class JOIN pg_inherits ON inhparent = oid WHERE inhrelid = ".$this->tableOid($R)." ORDER BY 1");}function
inheritedTables($R){return
get_vals("SELECT relname FROM pg_inherits JOIN pg_class ON inhrelid = oid WHERE inhparent = ".$this->tableOid($R)." ORDER BY 1");}function
partitionsInfo($R){$K=(min_version(10)?$this->conn->query("SELECT * FROM pg_partitioned_table WHERE partrelid = ".$this->tableOid($R))->fetch_assoc():null);if($K){$ya=get_vals("SELECT attname FROM pg_attribute WHERE attrelid = $K[partrelid] AND attnum IN (".str_replace(" ",", ",$K["partattrs"]).")");$Oa=array('h'=>'HASH','l'=>'LIST','r'=>'RANGE');return
array("partition_by"=>$Oa[$K["partstrat"]],"partition"=>implode(", ",array_map('Adminer\idf_escape',$ya)),);}return
array();}function
tableOid($R){return"(SELECT oid FROM pg_class WHERE relnamespace = $this->nsOid AND relname = ".q($R)." AND relkind IN ('r', 'm', 'v', 'f', 'p'))";}function
indexAlgorithms(array$yi){static$J=array();if(!$J)$J=get_vals("SELECT amname FROM pg_am".(min_version(9.6)?" WHERE amtype = 'i'":"")." ORDER BY amname = '".($this->conn->flavor=='cockroach'?"prefix":"btree")."' DESC, amname");return$J;}function
supportsIndex(array$S){return$S["Engine"]!="view";}function
hasCStyleEscapes(){static$Qa;if($Qa===null)$Qa=(get_val("SHOW standard_conforming_strings",0,$this->conn)=="off");return$Qa;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT datname FROM pg_database
WHERE datallowconn = TRUE AND has_database_privilege(datname, 'CONNECT')
ORDER BY datname");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return(preg_match('~^INTO~',$H)?limit($H,$Z,1,0,$Rh):" $H".(is_view(table_status1($R))?$Z:$Rh."WHERE ctid = (SELECT ctid FROM ".table($R).$Z.$Rh."LIMIT 1)"));}function
db_collation($j,$jb){return
get_val("SELECT datcollate FROM pg_database WHERE datname = ".q($j));}function
logged_user(){return
get_val("SELECT user");}function
tables_list(){$H="SELECT table_name, table_type FROM information_schema.tables WHERE table_schema = current_schema()";if(support("materializedview"))$H
.="
UNION ALL
SELECT matviewname, 'MATERIALIZED VIEW'
FROM pg_matviews
WHERE schemaname = current_schema()";$H
.="
ORDER BY 1";return
get_key_vals($H);}function
count_tables($i){$J=array();foreach($i
as$j){if(connection()->select_db($j))$J[$j]=count(tables_list());}return$J;}function
table_status($B=""){static$Fd;if($Fd===null)$Fd=get_val("SELECT 'pg_table_size'::regproc");$J=array();foreach(get_rows("SELECT
	relname AS \"Name\",
	CASE relkind WHEN 'v' THEN 'view' WHEN 'm' THEN 'materialized view' ELSE 'table' END AS \"Engine\"".($Fd?",
	pg_table_size(c.oid) AS \"Data_length\",
	pg_indexes_size(c.oid) AS \"Index_length\"":"").",
	obj_description(c.oid, 'pg_class') AS \"Comment\",
	".(min_version(12)?"''":"CASE WHEN relhasoids THEN 'oid' ELSE '' END")." AS \"Oid\",
	reltuples AS \"Rows\",
	".(min_version(10)?"relispartition::int AS partition,":"")."
	current_schema() AS nspname
FROM pg_class c
WHERE relkind IN ('r', 'm', 'v', 'f', 'p')
AND relnamespace = ".driver()->nsOid."
".($B!=""?"AND relname = ".q($B):"ORDER BY relname"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return
in_array($S["Engine"],array("view","materialized view"));}function
fk_support($S){return
true;}function
fields($R){$J=array();$ra=array('timestamp without time zone'=>'timestamp','timestamp with time zone'=>'timestamptz',);foreach(get_rows("SELECT
	a.attname AS field,
	format_type(a.atttypid, a.atttypmod) AS full_type,
	pg_get_expr(d.adbin, d.adrelid) AS default,
	a.attnotnull::int,
	col_description(a.attrelid, a.attnum) AS comment".(min_version(10)?",
	a.attidentity".(min_version(12)?",
	a.attgenerated":""):"")."
FROM pg_attribute a
LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
WHERE a.attrelid = ".driver()->tableOid($R)."
AND NOT a.attisdropped
AND a.attnum > 0
ORDER BY a.attnum")as$K){preg_match('~([^([]+)(\((.*)\))?([a-z ]+)?((\[[0-9]*])*)$~',$K["full_type"],$A);list(,$U,$y,$K["length"],$ka,$va)=$A;$K["length"].=$va;$Ya=$U.$ka;if(isset($ra[$Ya])){$K["type"]=$ra[$Ya];$K["full_type"]=$K["type"].$y.$va;}else{$K["type"]=$U;$K["full_type"]=$K["type"].$y.$ka.$va;}if(in_array($K['attidentity'],array('a','d')))$K['default']='GENERATED '.($K['attidentity']=='d'?'BY DEFAULT':'ALWAYS').' AS IDENTITY';$K["generated"]=($K["attgenerated"]=="s"?"STORED":"");$K["null"]=!$K["attnotnull"];$K["auto_increment"]=$K['attidentity']||preg_match('~^nextval\(~i',$K["default"])||preg_match('~^unique_rowid\(~',$K["default"]);$K["privileges"]=array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1);if(preg_match('~(.+)::[^,)]+(.*)~',$K["default"],$A))$K["default"]=($A[1]=="NULL"?null:idf_unescape($A[1]).$A[2]);$J[$K["field"]]=$K;}return$J;}function
indexes($R,$g=null){$g=connection($g);$J=array();$Ai=driver()->tableOid($R);$e=get_key_vals("SELECT attnum, attname FROM pg_attribute WHERE attrelid = $Ai AND attnum > 0",$g);foreach(get_rows("SELECT relname, indisunique::int, indisprimary::int, indkey, indoption, amname, pg_get_expr(indpred, indrelid, true) AS partial, pg_get_expr(indexprs, indrelid) AS indexpr
FROM pg_index
JOIN pg_class ON indexrelid = oid
JOIN pg_am ON pg_am.oid = pg_class.relam
WHERE indrelid = $Ai
ORDER BY indisprimary DESC, indisunique DESC",$g)as$K){$rh=$K["relname"];$J[$rh]["type"]=($K["partial"]?"INDEX":($K["indisprimary"]?"PRIMARY":($K["indisunique"]?"UNIQUE":"INDEX")));$J[$rh]["columns"]=array();$J[$rh]["descs"]=array();$J[$rh]["algorithm"]=$K["amname"];$J[$rh]["partial"]=$K["partial"];$ee=preg_split('~(?<=\)), (?=\()~',$K["indexpr"]);foreach(explode(" ",$K["indkey"])as$fe)$J[$rh]["columns"][]=($fe?$e[$fe]:array_shift($ee));foreach(explode(" ",$K["indoption"])as$ge)$J[$rh]["descs"][]=(intval($ge)&1?'1':null);$J[$rh]["lengths"]=array();}return$J;}function
foreign_keys($R){$J=array();foreach(get_rows("SELECT conname, condeferrable::int AS deferrable, pg_get_constraintdef(oid) AS definition
FROM pg_constraint
WHERE conrelid = ".driver()->tableOid($R)."
AND contype = 'f'::char
ORDER BY conkey, conname")as$K){if(preg_match('~FOREIGN KEY\s*\((.+)\)\s*REFERENCES (.+)\((.+)\)(.*)$~iA',$K['definition'],$A)){$K['source']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[1])));if(preg_match('~^(("([^"]|"")+"|[^"]+)\.)?"?("([^"]|"")+"|[^"]+)$~',$A[2],$Xe)){$K['ns']=idf_unescape($Xe[2]);$K['table']=idf_unescape($Xe[4]);}$K['target']=array_map('Adminer\idf_unescape',array_map('trim',explode(',',$A[3])));$K['on_delete']=(preg_match("~ON DELETE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$K['on_update']=(preg_match("~ON UPDATE (".driver()->onActions.")~",$A[4],$Xe)?$Xe[1]:'NO ACTION');$J[$K['conname']]=$K;}}return$J;}function
view($B){return
array("select"=>trim(get_val("SELECT pg_get_viewdef(".driver()->tableOid($B).")")));}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="information_schema";}function
error(){$J=h(connection()->error);if(preg_match('~^(.*\n)?([^\n]*)\n( *)\^(\n.*)?$~s',$J,$A))$J=$A[1].preg_replace('~((?:[^&]|&[^;]*;){'.strlen($A[3]).'})(.*)~','\1<b>\2</b>',$A[2]).$A[4];return
nl_br($J);}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" ENCODING ".idf_escape($c):""));}function
drop_databases($i){connection()->close();return
apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');}function
rename_database($B,$c){connection()->close();return
queries("ALTER DATABASE ".idf_escape(DB)." RENAME TO ".idf_escape($B));}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$eh=array();if($R!=""&&$R!=$B)$eh[]="ALTER TABLE ".table($R)." RENAME TO ".table($B);$Sh="";foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b[]="DROP $d";else{$Hj=$X[5];unset($X[5]);if($m[0]==""){if(isset($X[6]))$X[1]=($X[1]==" bigint"?" big":($X[1]==" smallint"?" small":" "))."serial";$b[]=($R!=""?"ADD ":"  ").implode($X);if(isset($X[6]))$b[]=($R!=""?"ADD":" ")." PRIMARY KEY ($X[0])";}else{if($d!=$X[0])$eh[]="ALTER TABLE ".table($B)." RENAME $d TO $X[0]";$b[]="ALTER $d TYPE$X[1]";$Th=$R."_".idf_unescape($X[0])."_seq";$b[]="ALTER $d ".($X[3]?"SET".preg_replace('~GENERATED ALWAYS(.*) STORED~','EXPRESSION\1',$X[3]):(isset($X[6])?"SET DEFAULT nextval(".q($Th).")":"DROP DEFAULT"));if(isset($X[6]))$Sh="CREATE SEQUENCE IF NOT EXISTS ".idf_escape($Th)." OWNED BY ".idf_escape($R).".$X[0]";$b[]="ALTER $d ".($X[2]==" NULL"?"DROP NOT":"SET").$X[2];}if($m[0]!=""||$Hj!="")$eh[]="COMMENT ON COLUMN ".table($B).".$X[0] IS ".($Hj!=""?substr($Hj,9):"''");}}$b=array_merge($b,$jd);if($R==""){$P="";if($E){$eb=(connection()->flavor=='cockroach');$P=" PARTITION BY $E[partition_by]($E[partition])";if($E["partition_by"]=='HASH'){$Cg=+$E["partitions"];for($s=0;$s<$Cg;$s++)$eh[]="CREATE TABLE ".idf_escape($B."_$s")." PARTITION OF ".idf_escape($B)." FOR VALUES WITH (MODULUS $Cg, REMAINDER $s)";}else{$Ug="MINVALUE";foreach($E["partition_names"]as$s=>$X){$Y=$E["partition_values"][$s];$zg=" VALUES ".($E["partition_by"]=='LIST'?"IN ($Y)":"FROM ($Ug) TO ($Y)");if($eb)$P
.=($s?",":" (")."\n  PARTITION ".(preg_match('~^DEFAULT$~i',$X)?$X:idf_escape($X))."$zg";else$eh[]="CREATE TABLE ".idf_escape($B."_$X")." PARTITION OF ".idf_escape($B)." FOR$zg";$Ug=$Y;}$P
.=($eb?"\n)":"");}}array_unshift($eh,"CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");}elseif($b)array_unshift($eh,"ALTER TABLE ".table($R)."\n".implode(",\n",$b));if($Sh)array_unshift($eh,$Sh);if($ob!==null)$eh[]="COMMENT ON TABLE ".table($B)." IS ".q($ob);foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
alter_indexes($R,$b){$h=array();$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX")$h[]=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R).($X[3]?" USING $X[3]":"")." (".implode(", ",$X[2]).")".($X[4]?" WHERE $X[4]":"");}if($h)array_unshift($eh,"ALTER TABLE ".table($R).implode(",",$h));if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
truncate_tables($T){return
queries("TRUNCATE ".implode(", ",array_map('Adminer\table',$T)));}function
drop_views($Nj){return
drop_tables($Nj);}function
drop_tables($T){foreach($T
as$R){$P=table_status1($R);if(!queries("DROP ".strtoupper($P["Engine"])." ".table($R)))return
false;}return
true;}function
move_tables($T,$Nj,$Ii){foreach(array_merge($T,$Nj)as$R){$P=table_status1($R);if(!queries("ALTER ".strtoupper($P["Engine"])." ".table($R)." SET SCHEMA ".idf_escape($Ii)))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array("Statement"=>"EXECUTE PROCEDURE ()");$e=array();$Z="WHERE trigger_schema = current_schema() AND event_object_table = ".q($R)." AND trigger_name = ".q($B);foreach(get_rows("SELECT * FROM information_schema.triggered_update_columns $Z")as$K)$e[]=$K["event_object_column"];$J=array();foreach(get_rows('SELECT trigger_name AS "Trigger", action_timing AS "Timing", event_manipulation AS "Event", \'FOR EACH \' || action_orientation AS "Type", action_statement AS "Statement"
FROM information_schema.triggers'."
$Z
ORDER BY event_manipulation DESC")as$K){if($e&&$K["Event"]=="UPDATE")$K["Event"].=" OF";$K["Of"]=implode(", ",$e);if($J)$K["Event"].=" OR $J[Event]";$J=$K;}return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT * FROM information_schema.triggers WHERE trigger_schema = current_schema() AND event_object_table = ".q($R))as$K){$hj=trigger($K["trigger_name"],$R);$J[$hj["Trigger"]]=array($hj["Timing"],$hj["Event"]);}return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","UPDATE OF","DELETE","INSERT OR UPDATE","INSERT OR UPDATE OF","DELETE OR INSERT","DELETE OR UPDATE","DELETE OR UPDATE OF","DELETE OR INSERT OR UPDATE","DELETE OR INSERT OR UPDATE OF"),"Type"=>array("FOR EACH ROW","FOR EACH STATEMENT"),);}function
routine($B,$U){$L=get_rows('SELECT routine_definition AS definition, LOWER(external_language) AS language, *
FROM information_schema.routines
WHERE routine_schema = current_schema() AND specific_name = '.q($B));$J=idx($L,0,array());$J["returns"]=array("type"=>$J["type_udt_name"]);$J["fields"]=get_rows('SELECT COALESCE(parameter_name, ordinal_position::text) AS field, data_type AS type, character_maximum_length AS length, parameter_mode AS inout
FROM information_schema.parameters
WHERE specific_schema = current_schema() AND specific_name = '.q($B).'
ORDER BY ordinal_position');return$J;}function
routines(){return
get_rows('SELECT specific_name AS "SPECIFIC_NAME", routine_type AS "ROUTINE_TYPE", routine_name AS "ROUTINE_NAME", type_udt_name AS "DTD_IDENTIFIER"
FROM information_schema.routines
WHERE routine_schema = current_schema()
ORDER BY SPECIFIC_NAME');}function
routine_languages(){return
get_vals("SELECT LOWER(lanname) FROM pg_catalog.pg_language");}function
routine_id($B,$K){$J=array();foreach($K["fields"]as$m){$y=$m["length"];$J[]=$m["type"].($y?"($y)":"");}return
idf_escape($B)."(".implode(", ",$J).")";}function
last_id($I){$K=(is_object($I)?$I->fetch_row():array());return($K?$K[0]:0);}function
explain($f,$H){return$f->query("EXPLAIN $H");}function
found_rows($S,$Z){if(preg_match("~ rows=([0-9]+)~",get_val("EXPLAIN SELECT * FROM ".idf_escape($S["Name"]).($Z?" WHERE ".implode(" AND ",$Z):"")),$qh))return$qh[1];}function
types(){return
get_key_vals("SELECT oid, typname
FROM pg_type
WHERE typnamespace = ".driver()->nsOid."
AND typtype IN ('b','d','e')
AND typelem = 0");}function
type_values($t){$Bc=get_vals("SELECT enumlabel FROM pg_enum WHERE enumtypid = $t ORDER BY enumsortorder");return($Bc?"'".implode("', '",array_map('addslashes',$Bc))."'":"");}function
schemas(){return
get_vals("SELECT nspname FROM pg_namespace ORDER BY nspname");}function
get_schema(){return
get_val("SELECT current_schema()");}function
set_schema($Fh,$g=null){if(!$g)$g=connection();$J=$g->query("SET search_path TO ".idf_escape($Fh));driver()->setUserTypes(types());return$J;}function
foreign_keys_sql($R){$J="";$P=table_status1($R);$fd=foreign_keys($R);ksort($fd);foreach($fd
as$ed=>$dd)$J
.="ALTER TABLE ONLY ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." ADD CONSTRAINT ".idf_escape($ed)." $dd[definition] ".($dd['deferrable']?'DEFERRABLE':'NOT DEFERRABLE').";\n";return($J?"$J\n":$J);}function
create_sql($R,$_a,$si){$wh=array();$Uh=array();$P=table_status1($R);if(is_view($P)){$Mj=view($R);return
rtrim("CREATE VIEW ".idf_escape($R)." AS $Mj[select]",";");}$n=fields($R);if(count($P)<2||empty($n))return
false;$J="CREATE TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." (\n    ";foreach($n
as$m){$xg=idf_escape($m['field']).' '.$m['full_type'].default_value($m).($m['null']?"":" NOT NULL");$wh[]=$xg;if(preg_match('~nextval\(\'([^\']+)\'\)~',$m['default'],$Ze)){$Th=$Ze[1];$hi=first(get_rows((min_version(10)?"SELECT *, cache_size AS cache_value FROM pg_sequences WHERE schemaname = current_schema() AND sequencename = ".q(idf_unescape($Th)):"SELECT * FROM $Th"),null,"-- "));$Uh[]=($si=="DROP+CREATE"?"DROP SEQUENCE IF EXISTS $Th;\n":"")."CREATE SEQUENCE $Th INCREMENT $hi[increment_by] MINVALUE $hi[min_value] MAXVALUE $hi[max_value]".($_a&&$hi['last_value']?" START ".($hi["last_value"]+1):"")." CACHE $hi[cache_value];";}}if(!empty($Uh))$J=implode("\n\n",$Uh)."\n\n$J";$G="";foreach(indexes($R)as$ce=>$v){if($v['type']=='PRIMARY'){$G=$ce;$wh[]="CONSTRAINT ".idf_escape($ce)." PRIMARY KEY (".implode(', ',array_map('Adminer\idf_escape',$v['columns'])).")";}}foreach(driver()->checkConstraints($R)as$ub=>$wb)$wh[]="CONSTRAINT ".idf_escape($ub)." CHECK $wb";$J
.=implode(",\n    ",$wh)."\n)";$zg=driver()->partitionsInfo($P['Name']);if($zg)$J
.="\nPARTITION BY $zg[partition_by]($zg[partition])";$J
.="\nWITH (oids = ".($P['Oid']?'true':'false').");";if($P['Comment'])$J
.="\n\nCOMMENT ON TABLE ".idf_escape($P['nspname']).".".idf_escape($P['Name'])." IS ".q($P['Comment']).";";foreach($n
as$Wc=>$m){if($m['comment'])$J
.="\n\nCOMMENT ON COLUMN ".idf_escape($P['nspname']).".".idf_escape($P['Name']).".".idf_escape($Wc)." IS ".q($m['comment']).";";}foreach(get_rows("SELECT indexdef FROM pg_catalog.pg_indexes WHERE schemaname = current_schema() AND tablename = ".q($R).($G?" AND indexname != ".q($G):""),null,"-- ")as$K)$J
.="\n\n$K[indexdef];";return
rtrim($J,';');}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
trigger_sql($R){$P=table_status1($R);$J="";foreach(triggers($R)as$gj=>$fj){$hj=trigger($gj,$P['Name']);$J
.="\nCREATE TRIGGER ".idf_escape($hj['Trigger'])." $hj[Timing] $hj[Event] ON ".idf_escape($P["nspname"]).".".idf_escape($P['Name'])." $hj[Type] $hj[Statement];;\n";}return$J;}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)){if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="CREATE DATABASE $B;\n";}return"$J\\connect $B";}function
show_variables(){return
get_rows("SHOW ALL");}function
process_list(){return
get_rows("SELECT * FROM pg_stat_activity ORDER BY ".(min_version(9.2)?"pid":"procpid"));}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|columns|comment|database|drop_col|dump|descidx|indexes|kill|partial_indexes|routine|scheme|sequence|sql|table|trigger|type|variables|view'.(min_version(9.3)?'|materializedview':'').(min_version(11)?'|procedure':'').(connection()->flavor=='cockroach'?'':'|processlist').')$~',$Uc);}function
kill_process($X){return
queries("SELECT pg_terminate_backend(".number($X).")");}function
connection_id(){return"SELECT pg_backend_pid()";}function
max_connections(){return
get_val("SHOW max_connections");}}add_driver("oracle","Oracle (beta)");if(isset($_GET["oracle"])){define('Adminer\DRIVER',"oracle");if(extension_loaded("oci8")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="oci8";var$_current_db;private$link;function
_error($Cc,$l){if(ini_bool("html_errors"))$l=html_entity_decode(strip_tags($l));$l=preg_replace('~^[^:]*: ~','',$l);$this->error=$l;}function
attach($N,$V,$F){$this->link=@oci_new_connect($V,$F,$N,"AL32UTF8");if($this->link){$this->server_info=oci_server_version($this->link);return'';}$l=oci_error();return$l["message"];}function
quote($Q){return"'".str_replace("'","''",$Q)."'";}function
select_db($Nb){$this->_current_db=$Nb;return
true;}function
query($H,$oj=false){$I=oci_parse($this->link,$H);$this->error="";if(!$I){$l=oci_error($this->link);$this->errno=$l["code"];$this->error=$l["message"];return
false;}set_error_handler(array($this,'_error'));$J=@oci_execute($I);restore_error_handler();if($J){if(oci_num_fields($I))return
new
Result($I);$this->affected_rows=oci_num_rows($I);oci_free_statement($I);}return$J;}function
timeout($uf){return
oci_set_call_timeout($this->link,$uf);}}class
Result{var$num_rows;private$result,$offset=1;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'OCILob')||is_a($X,'OCI-Lob'))$K[$x]=$X->load();}return$K;}function
fetch_assoc(){return$this->convert(oci_fetch_assoc($this->result));}function
fetch_row(){return$this->convert(oci_fetch_row($this->result));}function
fetch_field(){$d=$this->offset++;$J=new
\stdClass;$J->name=oci_field_name($this->result,$d);$J->type=oci_field_type($this->result,$d);$J->charsetnr=(preg_match("~raw|blob|bfile~",$J->type)?63:0);return$J;}function
__destruct(){oci_free_statement($this->result);}}}elseif(extension_loaded("pdo_oci")){class
Db
extends
PdoDb{var$extension="PDO_OCI";var$_current_db;function
attach($N,$V,$F){return$this->dsn("oci:dbname=//$N;charset=AL32UTF8",$V,$F);}function
select_db($Nb){$this->_current_db=$Nb;return
true;}}}class
Driver
extends
SqlDriver{static$extensions=array("OCI8","PDO_OCI");static$jush="oracle";var$insertFunctions=array("date"=>"current_date","timestamp"=>"current_timestamp",);var$editFunctions=array("number|float|double"=>"+/-","date|timestamp"=>"+ interval/- interval","char|clob"=>"||",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL","SQL");var$functions=array("length","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("number"=>38,"binary_float"=>12,"binary_double"=>21),'Date and time'=>array("date"=>10,"timestamp"=>29,"interval year"=>12,"interval day"=>28),'Strings'=>array("char"=>2000,"varchar2"=>4000,"nchar"=>2000,"nvarchar2"=>4000,"clob"=>4294967295,"nclob"=>4294967295),'Binary'=>array("raw"=>2000,"long raw"=>2147483648,"blob"=>4294967295,"bfile"=>4294967296),);}function
begin(){return
true;}function
insertUpdate($R,array$L,array$G){foreach($L
as$O){$wj=array();$Z=array();foreach($O
as$x=>$X){$wj[]="$x = $X";if(isset($G[idf_unescape($x)]))$Z[]="$x = $X";}if(!(($Z&&queries("UPDATE ".table($R)." SET ".implode(", ",$wj)." WHERE ".implode(" AND ",$Z))&&$this->conn->affected_rows)||queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES (".implode(", ",$O).")")))return
false;}return
true;}function
hasCStyleEscapes(){return
true;}}function
idf_escape($u){return'"'.str_replace('"','""',$u).'"';}function
table($u){return
idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT DISTINCT tablespace_name FROM (
SELECT tablespace_name FROM user_tablespaces
UNION SELECT tablespace_name FROM all_tables WHERE tablespace_name IS NOT NULL
)
ORDER BY 1");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($C?" * FROM (SELECT t.*, rownum AS rnum FROM (SELECT $H$Z) t WHERE rownum <= ".($z+$C).") WHERE rnum > $C":($z?" * FROM (SELECT $H$Z) WHERE rownum <= ".($z+$C):" $H$Z"));}function
limit1($R,$H,$Z,$Rh="\n"){return" $H$Z";}function
db_collation($j,$jb){return
get_val("SELECT value FROM nls_database_parameters WHERE parameter = 'NLS_CHARACTERSET'");}function
logged_user(){return
get_val("SELECT USER FROM DUAL");}function
get_current_db(){$j=connection()->_current_db?:DB;unset(connection()->_current_db);return$j;}function
where_owner($Sg,$rg="owner"){if(!$_GET["ns"])return'';return"$Sg$rg = sys_context('USERENV', 'CURRENT_SCHEMA')";}function
views_table($e){$rg=where_owner('');return"(SELECT $e FROM all_views WHERE ".($rg?:"rownum < 0").")";}function
tables_list(){$Mj=views_table("view_name");$rg=where_owner(" AND ");return
get_key_vals("SELECT table_name, 'table' FROM all_tables WHERE tablespace_name = ".q(DB)."$rg
UNION SELECT view_name, 'view' FROM $Mj
ORDER BY 1");}function
count_tables($i){$J=array();foreach($i
as$j)$J[$j]=get_val("SELECT COUNT(*) FROM all_tables WHERE tablespace_name = ".q($j));return$J;}function
table_status($B=""){$J=array();$Kh=q($B);$j=get_current_db();$Mj=views_table("view_name");$rg=where_owner(" AND ");foreach(get_rows('SELECT table_name "Name", \'table\' "Engine", avg_row_len * num_rows "Data_length", num_rows "Rows" FROM all_tables WHERE tablespace_name = '.q($j).$rg.($B!=""?" AND table_name = $Kh":"")."
UNION SELECT view_name, 'view', 0, 0 FROM $Mj".($B!=""?" WHERE view_name = $Kh":"")."
ORDER BY 1")as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="view";}function
fk_support($S){return
true;}function
fields($R){$J=array();$rg=where_owner(" AND ");foreach(get_rows("SELECT * FROM all_tab_columns WHERE table_name = ".q($R)."$rg ORDER BY column_id")as$K){$U=$K["DATA_TYPE"];$y="$K[DATA_PRECISION],$K[DATA_SCALE]";if($y==",")$y=$K["CHAR_COL_DECL_LENGTH"];$J[$K["COLUMN_NAME"]]=array("field"=>$K["COLUMN_NAME"],"full_type"=>$U.($y?"($y)":""),"type"=>strtolower($U),"length"=>$y,"default"=>$K["DATA_DEFAULT"],"null"=>($K["NULLABLE"]=="Y"),"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),);}return$J;}function
indexes($R,$g=null){$J=array();$rg=where_owner(" AND ","aic.table_owner");foreach(get_rows("SELECT aic.*, ac.constraint_type, atc.data_default
FROM all_ind_columns aic
LEFT JOIN all_constraints ac ON aic.index_name = ac.constraint_name AND aic.table_name = ac.table_name AND aic.index_owner = ac.owner
LEFT JOIN all_tab_cols atc ON aic.column_name = atc.column_name AND aic.table_name = atc.table_name AND aic.index_owner = atc.owner
WHERE aic.table_name = ".q($R)."$rg
ORDER BY ac.constraint_type, aic.column_position",$g)as$K){$ce=$K["INDEX_NAME"];$lb=$K["DATA_DEFAULT"];$lb=($lb?trim($lb,'"'):$K["COLUMN_NAME"]);$J[$ce]["type"]=($K["CONSTRAINT_TYPE"]=="P"?"PRIMARY":($K["CONSTRAINT_TYPE"]=="U"?"UNIQUE":"INDEX"));$J[$ce]["columns"][]=$lb;$J[$ce]["lengths"][]=($K["CHAR_LENGTH"]&&$K["CHAR_LENGTH"]!=$K["COLUMN_LENGTH"]?$K["CHAR_LENGTH"]:null);$J[$ce]["descs"][]=($K["DESCEND"]&&$K["DESCEND"]=="DESC"?'1':null);}return$J;}function
view($B){$Mj=views_table("view_name, text");$L=get_rows('SELECT text "select" FROM '.$Mj.' WHERE view_name = '.q($B));return
reset($L);}function
collations(){return
array();}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
h(connection()->error);}function
explain($f,$H){$f->query("EXPLAIN PLAN FOR $H");return$f->query("SELECT * FROM plan_table");}function
found_rows($S,$Z){}function
auto_increment(){return"";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=$ic=array();$kg=($R?fields($R):array());foreach($n
as$m){$X=$m[1];if($X&&$m[0]!=""&&idf_escape($m[0])!=$X[0])queries("ALTER TABLE ".table($R)." RENAME COLUMN ".idf_escape($m[0])." TO $X[0]");$jg=$kg[$m[0]];if($X&&$jg){$Nf=process_field($jg,$jg);if($X[2]==$Nf[2])$X[2]="";}if($X)$b[]=($R!=""?($m[0]!=""?"MODIFY (":"ADD ("):"  ").implode($X).($R!=""?")":"");else$ic[]=idf_escape($m[0]);}if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)");return(!$b||queries("ALTER TABLE ".table($R)."\n".implode("\n",$b)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP (".implode(", ",$ic).")"))&&($R==$B||queries("ALTER TABLE ".table($R)." RENAME TO ".table($B)));}function
alter_indexes($R,$b){$ic=array();$eh=array();foreach($b
as$X){if($X[0]!="INDEX"){$X[2]=preg_replace('~ DESC$~','',$X[2]);$h=($X[2]=="DROP"?"\nDROP CONSTRAINT ".idf_escape($X[1]):"\nADD".($X[1]!=""?" CONSTRAINT ".idf_escape($X[1]):"")." $X[0] ".($X[0]=="PRIMARY"?"KEY ":"")."(".implode(", ",$X[2]).")");array_unshift($eh,"ALTER TABLE ".table($R).$h);}elseif($X[2]=="DROP")$ic[]=idf_escape($X[1]);else$eh[]="CREATE INDEX ".idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R)." (".implode(", ",$X[2]).")";}if($ic)array_unshift($eh,"DROP INDEX ".implode(", ",$ic));foreach($eh
as$H){if(!queries($H))return
false;}return
true;}function
foreign_keys($R){$J=array();$H="SELECT c_list.CONSTRAINT_NAME as NAME,
c_src.COLUMN_NAME as SRC_COLUMN,
c_dest.OWNER as DEST_DB,
c_dest.TABLE_NAME as DEST_TABLE,
c_dest.COLUMN_NAME as DEST_COLUMN,
c_list.DELETE_RULE as ON_DELETE
FROM ALL_CONSTRAINTS c_list, ALL_CONS_COLUMNS c_src, ALL_CONS_COLUMNS c_dest
WHERE c_list.CONSTRAINT_NAME = c_src.CONSTRAINT_NAME
AND c_list.R_CONSTRAINT_NAME = c_dest.CONSTRAINT_NAME
AND c_list.CONSTRAINT_TYPE = 'R'
AND c_src.TABLE_NAME = ".q($R);foreach(get_rows($H)as$K)$J[$K['NAME']]=array("db"=>$K['DEST_DB'],"table"=>$K['DEST_TABLE'],"source"=>array($K['SRC_COLUMN']),"target"=>array($K['DEST_COLUMN']),"on_delete"=>$K['ON_DELETE'],"on_update"=>null,);return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
apply_queries("DROP VIEW",$Nj);}function
drop_tables($T){return
apply_queries("DROP TABLE",$T);}function
last_id($I){return
0;}function
schemas(){$J=get_vals("SELECT DISTINCT owner FROM dba_segments WHERE owner IN (SELECT username FROM dba_users WHERE default_tablespace NOT IN ('SYSTEM','SYSAUX')) ORDER BY 1");return($J?:get_vals("SELECT DISTINCT owner FROM all_tables WHERE tablespace_name = ".q(DB)." ORDER BY 1"));}function
get_schema(){return
get_val("SELECT sys_context('USERENV', 'SESSION_USER') FROM dual");}function
set_schema($Hh,$g=null){if(!$g)$g=connection();return$g->query("ALTER SESSION SET CURRENT_SCHEMA = ".idf_escape($Hh));}function
show_variables(){return
get_rows('SELECT name, display_value FROM v$parameter');}function
show_status(){$J=array();$L=get_rows('SELECT * FROM v$instance');foreach(reset($L)as$x=>$X)$J[]=array($x,$X);return$J;}function
process_list(){return
get_rows('SELECT
	sess.process AS "process",
	sess.username AS "user",
	sess.schemaname AS "schema",
	sess.status AS "status",
	sess.wait_class AS "wait_class",
	sess.seconds_in_wait AS "seconds_in_wait",
	sql.sql_text AS "sql_text",
	sess.machine AS "machine",
	sess.port AS "port"
FROM v$session sess LEFT OUTER JOIN v$sql sql
ON sql.sql_id = sess.sql_id
WHERE sess.type = \'USER\'
ORDER BY PROCESS
');}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(columns|database|drop_col|indexes|descidx|processlist|scheme|sql|status|table|variables|view)$~',$Uc);}}add_driver("mssql","MS SQL");if(isset($_GET["mssql"])){define('Adminer\DRIVER',"mssql");if(extension_loaded("sqlsrv")&&$_GET["ext"]!="pdo"){class
Db
extends
SqlDb{var$extension="sqlsrv";private$link,$result;private
function
get_error(){$this->error="";foreach(sqlsrv_errors()as$l){$this->errno=$l["code"];$this->error
.="$l[message]\n";}$this->error=rtrim($this->error);}function
attach($N,$V,$F){$vb=array("UID"=>$V,"PWD"=>$F,"CharacterSet"=>"UTF-8");$ni=adminer()->connectSsl();if(isset($ni["Encrypt"]))$vb["Encrypt"]=$ni["Encrypt"];if(isset($ni["TrustServerCertificate"]))$vb["TrustServerCertificate"]=$ni["TrustServerCertificate"];$j=adminer()->database();if($j!="")$vb["Database"]=$j;list($Md,$Mg)=host_port($N);$this->link=@sqlsrv_connect($Md.($Mg?",$Mg":""),$vb);if($this->link){$he=sqlsrv_server_info($this->link);$this->server_info=$he['SQLServerVersion'];}else$this->get_error();return($this->link?'':$this->error);}function
quote($Q){$pj=strlen($Q)!=strlen(utf8_decode($Q));return($pj?"N":"")."'".str_replace("'","''",$Q)."'";}function
select_db($Nb){return$this->query(use_sql($Nb));}function
query($H,$oj=false){$I=sqlsrv_query($this->link,$H);$this->error="";if(!$I){$this->get_error();return
false;}return$this->store_result($I);}function
multi_query($H){$this->result=sqlsrv_query($this->link,$H);$this->error="";if(!$this->result){$this->get_error();return
false;}return
true;}function
store_result($I=null){if(!$I)$I=$this->result;if(!$I)return
false;if(sqlsrv_field_metadata($I))return
new
Result($I);$this->affected_rows=sqlsrv_rows_affected($I);return
true;}function
next_result(){return$this->result?!!sqlsrv_next_result($this->result):false;}}class
Result{var$num_rows;private$result,$offset=0,$fields;function
__construct($I){$this->result=$I;}private
function
convert($K){foreach((array)$K
as$x=>$X){if(is_a($X,'DateTime'))$K[$x]=$X->format("Y-m-d H:i:s");}return$K;}function
fetch_assoc(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_ASSOC));}function
fetch_row(){return$this->convert(sqlsrv_fetch_array($this->result,SQLSRV_FETCH_NUMERIC));}function
fetch_field(){if(!$this->fields)$this->fields=sqlsrv_field_metadata($this->result);$m=$this->fields[$this->offset++];$J=new
\stdClass;$J->name=$m["Name"];$J->type=($m["Type"]==1?254:15);$J->charsetnr=0;return$J;}function
seek($C){for($s=0;$s<$C;$s++)sqlsrv_fetch($this->result);}function
__destruct(){sqlsrv_free_stmt($this->result);}}function
last_id($I){return
get_val("SELECT SCOPE_IDENTITY()");}function
explain($f,$H){$f->query("SET SHOWPLAN_ALL ON");$J=$f->query($H);$f->query("SET SHOWPLAN_ALL OFF");return$J;}}else{abstract
class
MssqlDb
extends
PdoDb{function
select_db($Nb){return$this->query(use_sql($Nb));}function
lastInsertId(){return$this->pdo->lastInsertId();}}function
last_id($I){return
connection()->lastInsertId();}function
explain($f,$H){}if(extension_loaded("pdo_sqlsrv")){class
Db
extends
MssqlDb{var$extension="PDO_SQLSRV";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("sqlsrv:Server=$Md".($Mg?",$Mg":""),$V,$F);}}}elseif(extension_loaded("pdo_dblib")){class
Db
extends
MssqlDb{var$extension="PDO_DBLIB";function
attach($N,$V,$F){list($Md,$Mg)=host_port($N);return$this->dsn("dblib:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F);}}}}class
Driver
extends
SqlDriver{static$extensions=array("SQLSRV","PDO_SQLSRV","PDO_DBLIB");static$jush="mssql";var$insertFunctions=array("date|time"=>"getdate");var$editFunctions=array("int|decimal|real|float|money|datetime"=>"+/-","char|text"=>"+",);var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","IN","IS NULL","NOT LIKE","NOT IN","IS NOT NULL");var$functions=array("len","lower","round","upper");var$grouping=array("avg","count","count distinct","max","min","sum");var$generated=array("PERSISTED","VIRTUAL");var$onActions="NO ACTION|CASCADE|SET NULL|SET DEFAULT";static
function
connect($N,$V,$F){if($N=="")$N="localhost:1433";return
parent::connect($N,$V,$F);}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"int"=>10,"bigint"=>20,"bit"=>1,"decimal"=>0,"real"=>12,"float"=>53,"smallmoney"=>10,"money"=>20),'Date and time'=>array("date"=>10,"smalldatetime"=>19,"datetime"=>19,"datetime2"=>19,"time"=>8,"datetimeoffset"=>10),'Strings'=>array("char"=>8000,"varchar"=>8000,"text"=>2147483647,"nchar"=>4000,"nvarchar"=>4000,"ntext"=>1073741823),'Binary'=>array("binary"=>8000,"varbinary"=>8000,"image"=>2147483647),);}function
insertUpdate($R,array$L,array$G){$n=fields($R);$wj=array();$Z=array();$O=reset($L);$e="c".implode(", c",range(1,count($O)));$Pa=0;$ne=array();foreach($O
as$x=>$X){$Pa++;$B=idf_unescape($x);if(!$n[$B]["auto_increment"])$ne[$x]="c$Pa";if(isset($G[$B]))$Z[]="$x = c$Pa";else$wj[]="$x = c$Pa";}$Ij=array();foreach($L
as$O)$Ij[]="(".implode(", ",$O).")";if($Z){$Rd=queries("SET IDENTITY_INSERT ".table($R)." ON");$J=queries("MERGE ".table($R)." USING (VALUES\n\t".implode(",\n\t",$Ij)."\n) AS source ($e) ON ".implode(" AND ",$Z).($wj?"\nWHEN MATCHED THEN UPDATE SET ".implode(", ",$wj):"")."\nWHEN NOT MATCHED THEN INSERT (".implode(", ",array_keys($Rd?$O:$ne)).") VALUES (".($Rd?$e:implode(", ",$ne)).");");if($Rd)queries("SET IDENTITY_INSERT ".table($R)." OFF");}else$J=queries("INSERT INTO ".table($R)." (".implode(", ",array_keys($O)).") VALUES\n".implode(",\n",$Ij));return$J;}function
begin(){return
queries("BEGIN TRANSACTION");}function
tableHelp($B,$ye=false){$Re=array("sys"=>"catalog-views/sys-","INFORMATION_SCHEMA"=>"information-schema-views/",);$_=$Re[get_schema()];if($_)return"relational-databases/system-$_".preg_replace('~_~','-',strtolower($B))."-transact-sql";}}function
idf_escape($u){return"[".str_replace("]","]]",$u)."]";}function
table($u){return($_GET["ns"]!=""?idf_escape($_GET["ns"]).".":"").idf_escape($u);}function
get_databases($hd){return
get_vals("SELECT name FROM sys.databases WHERE name NOT IN ('master', 'tempdb', 'model', 'msdb')");}function
limit($H,$Z,$z,$C=0,$Rh=" "){return($z?" TOP (".($z+$C).")":"")." $H$Z";}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,$jb){return
get_val("SELECT collation_name FROM sys.databases WHERE name = ".q($j));}function
logged_user(){return
get_val("SELECT SUSER_NAME()");}function
tables_list(){return
get_key_vals("SELECT name, type_desc FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ORDER BY name");}function
count_tables($i){$J=array();foreach($i
as$j){connection()->select_db($j);$J[$j]=get_val("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES");}return$J;}function
table_status($B=""){$J=array();foreach(get_rows("SELECT ao.name AS Name, ao.type_desc AS Engine, (SELECT value FROM fn_listextendedproperty(default, 'SCHEMA', schema_name(schema_id), 'TABLE', ao.name, null, null)) AS Comment
FROM sys.all_objects AS ao
WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') ".($B!=""?"AND name = ".q($B):"ORDER BY name"))as$K)$J[$K["Name"]]=$K;return$J;}function
is_view($S){return$S["Engine"]=="VIEW";}function
fk_support($S){return
true;}function
fields($R){$qb=get_key_vals("SELECT objname, cast(value as varchar(max)) FROM fn_listextendedproperty('MS_DESCRIPTION', 'schema', ".q(get_schema()).", 'table', ".q($R).", 'column', NULL)");$J=array();$zi=get_val("SELECT object_id FROM sys.all_objects WHERE schema_id = SCHEMA_ID(".q(get_schema()).") AND type IN ('S', 'U', 'V') AND name = ".q($R));foreach(get_rows("SELECT c.max_length, c.precision, c.scale, c.name, c.is_nullable, c.is_identity, c.collation_name, t.name type, d.definition [default], d.name default_constraint, i.is_primary_key
FROM sys.all_columns c
JOIN sys.types t ON c.user_type_id = t.user_type_id
LEFT JOIN sys.default_constraints d ON c.default_object_id = d.object_id
LEFT JOIN sys.index_columns ic ON c.object_id = ic.object_id AND c.column_id = ic.column_id
LEFT JOIN sys.indexes i ON ic.object_id = i.object_id AND ic.index_id = i.index_id
WHERE c.object_id = ".q($zi))as$K){$U=$K["type"];$y=(preg_match("~char|binary~",$U)?intval($K["max_length"])/($U[0]=='n'?2:1):($U=="decimal"?"$K[precision],$K[scale]":""));$J[$K["name"]]=array("field"=>$K["name"],"full_type"=>$U.($y?"($y)":""),"type"=>$U,"length"=>$y,"default"=>(preg_match("~^\('(.*)'\)$~",$K["default"],$A)?str_replace("''","'",$A[1]):$K["default"]),"default_constraint"=>$K["default_constraint"],"null"=>$K["is_nullable"],"auto_increment"=>$K["is_identity"],"collation"=>$K["collation_name"],"privileges"=>array("insert"=>1,"select"=>1,"update"=>1,"where"=>1,"order"=>1),"primary"=>$K["is_primary_key"],"comment"=>$qb[$K["name"]],);}foreach(get_rows("SELECT * FROM sys.computed_columns WHERE object_id = ".q($zi))as$K){$J[$K["name"]]["generated"]=($K["is_persisted"]?"PERSISTED":"VIRTUAL");$J[$K["name"]]["default"]=$K["definition"];}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SELECT i.name, key_ordinal, is_unique, is_primary_key, c.name AS column_name, is_descending_key
FROM sys.indexes i
INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
WHERE OBJECT_NAME(i.object_id) = ".q($R),$g)as$K){$B=$K["name"];$J[$B]["type"]=($K["is_primary_key"]?"PRIMARY":($K["is_unique"]?"UNIQUE":"INDEX"));$J[$B]["lengths"]=array();$J[$B]["columns"][$K["key_ordinal"]]=$K["column_name"];$J[$B]["descs"][$K["key_ordinal"]]=($K["is_descending_key"]?'1':null);}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^[]|\[[^]]*])*\s+AS\s+~isU','',get_val("SELECT VIEW_DEFINITION FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = SCHEMA_NAME() AND TABLE_NAME = ".q($B))));}function
collations(){$J=array();foreach(get_vals("SELECT name FROM fn_helpcollations()")as$c)$J[preg_replace('~_.*~','',$c)][]=$c;return$J;}function
information_schema($j){return
get_schema()=="INFORMATION_SCHEMA";}function
error(){return
nl_br(h(preg_replace('~^(\[[^]]*])+~m','',connection()->error)));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).(preg_match('~^[a-z0-9_]+$~i',$c)?" COLLATE $c":""));}function
drop_databases($i){return
queries("DROP DATABASE ".implode(", ",array_map('Adminer\idf_escape',$i)));}function
rename_database($B,$c){if(preg_match('~^[a-z0-9_]+$~i',$c))queries("ALTER DATABASE ".idf_escape(DB)." COLLATE $c");queries("ALTER DATABASE ".idf_escape(DB)." MODIFY NAME = ".idf_escape($B));return
true;}function
auto_increment(){return" IDENTITY".($_POST["Auto_increment"]!=""?"(".number($_POST["Auto_increment"]).",1)":"")." PRIMARY KEY";}function
alter_table($R,$B,$n,$jd,$ob,$xc,$c,$_a,$E){$b=array();$qb=array();$kg=fields($R);foreach($n
as$m){$d=idf_escape($m[0]);$X=$m[1];if(!$X)$b["DROP"][]=" COLUMN $d";else{$X[1]=preg_replace("~( COLLATE )'(\\w+)'~",'\1\2',$X[1]);$qb[$m[0]]=$X[5];unset($X[5]);if(preg_match('~ AS ~',$X[3]))unset($X[1],$X[2]);if($m[0]=="")$b["ADD"][]="\n  ".implode("",$X).($R==""?substr($jd[$X[0]],16+strlen($X[0])):"");else{$k=$X[3];unset($X[3]);unset($X[6]);if($d!=$X[0])queries("EXEC sp_rename ".q(table($R).".$d").", ".q(idf_unescape($X[0])).", 'COLUMN'");$b["ALTER COLUMN ".implode("",$X)][]="";$jg=$kg[$m[0]];if(default_value($jg)!=$k){if($jg["default"]!==null)$b["DROP"][]=" ".idf_escape($jg["default_constraint"]);if($k)$b["ADD"][]="\n $k FOR $d";}}}}if($R=="")return
queries("CREATE TABLE ".table($B)." (".implode(",",(array)$b["ADD"])."\n)");if($R!=$B)queries("EXEC sp_rename ".q(table($R)).", ".q($B));if($jd)$b[""]=$jd;foreach($b
as$x=>$X){if(!queries("ALTER TABLE ".table($B)." $x".implode(",",$X)))return
false;}foreach($qb
as$x=>$X){$ob=substr($X,9);queries("EXEC sp_dropextendedproperty @name = N'MS_Description', @level0type = N'Schema', @level0name = ".q(get_schema()).", @level1type = N'Table', @level1name = ".q($B).", @level2type = N'Column', @level2name = ".q($x));queries("EXEC sp_addextendedproperty
@name = N'MS_Description',
@value = $ob,
@level0type = N'Schema',
@level0name = ".q(get_schema()).",
@level1type = N'Table',
@level1name = ".q($B).",
@level2type = N'Column',
@level2name = ".q($x));}return
true;}function
alter_indexes($R,$b){$v=array();$ic=array();foreach($b
as$X){if($X[2]=="DROP"){if($X[0]=="PRIMARY")$ic[]=idf_escape($X[1]);else$v[]=idf_escape($X[1])." ON ".table($R);}elseif(!queries(($X[0]!="PRIMARY"?"CREATE $X[0] ".($X[0]!="INDEX"?"INDEX ":"").idf_escape($X[1]!=""?$X[1]:uniqid($R."_"))." ON ".table($R):"ALTER TABLE ".table($R)." ADD PRIMARY KEY")." (".implode(", ",$X[2]).")"))return
false;}return(!$v||queries("DROP INDEX ".implode(", ",$v)))&&(!$ic||queries("ALTER TABLE ".table($R)." DROP ".implode(", ",$ic)));}function
found_rows($S,$Z){}function
foreign_keys($R){$J=array();$Uf=array("CASCADE","NO ACTION","SET NULL","SET DEFAULT");foreach(get_rows("EXEC sp_fkeys @fktable_name = ".q($R).", @fktable_owner = ".q(get_schema()))as$K){$p=&$J[$K["FK_NAME"]];$p["db"]=$K["PKTABLE_QUALIFIER"];$p["ns"]=$K["PKTABLE_OWNER"];$p["table"]=$K["PKTABLE_NAME"];$p["on_update"]=$Uf[$K["UPDATE_RULE"]];$p["on_delete"]=$Uf[$K["DELETE_RULE"]];$p["source"][]=$K["FKCOLUMN_NAME"];$p["target"][]=$K["PKCOLUMN_NAME"];}return$J;}function
truncate_tables($T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views($Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables($T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables($T,$Nj,$Ii){return
apply_queries("ALTER SCHEMA ".idf_escape($Ii)." TRANSFER",array_merge($T,$Nj));}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SELECT s.name [Trigger],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(s.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(s.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(s.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing],
c.text
FROM sysobjects s
JOIN syscomments c ON s.id = c.id
WHERE s.xtype = 'TR' AND s.name = ".q($B));$J=reset($L);if($J)$J["Statement"]=preg_replace('~^.+\s+AS\s+~isU','',$J["text"]);return$J;}function
triggers($R){$J=array();foreach(get_rows("SELECT sys1.name,
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsertTrigger') = 1 THEN 'INSERT' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsUpdateTrigger') = 1 THEN 'UPDATE' WHEN OBJECTPROPERTY(sys1.id, 'ExecIsDeleteTrigger') = 1 THEN 'DELETE' END [Event],
CASE WHEN OBJECTPROPERTY(sys1.id, 'ExecIsInsteadOfTrigger') = 1 THEN 'INSTEAD OF' ELSE 'AFTER' END [Timing]
FROM sysobjects sys1
JOIN sysobjects sys2 ON sys1.parent_obj = sys2.id
WHERE sys1.xtype = 'TR' AND sys2.name = ".q($R))as$K)$J[$K["name"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("AFTER","INSTEAD OF"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("AS"),);}function
schemas(){return
get_vals("SELECT name FROM sys.schemas");}function
get_schema(){if($_GET["ns"]!="")return$_GET["ns"];return
get_val("SELECT SCHEMA_NAME()");}function
set_schema($Fh){$_GET["ns"]=$Fh;return
true;}function
create_sql($R,$_a,$si){if(is_view(table_status1($R))){$Mj=view($R);return"CREATE VIEW ".table($R)." AS $Mj[select]";}$n=array();$G=false;foreach(fields($R)as$B=>$m){$X=process_field($m,$m);if($X[6])$G=true;$n[]=implode("",$X);}foreach(indexes($R)as$B=>$v){if(!$G||$v["type"]!="PRIMARY"){$e=array();foreach($v["columns"]as$x=>$X)$e[]=idf_escape($X).($v["descs"][$x]?" DESC":"");$B=idf_escape($B);$n[]=($v["type"]=="INDEX"?"INDEX $B":"CONSTRAINT $B ".($v["type"]=="UNIQUE"?"UNIQUE":"PRIMARY KEY"))." (".implode(", ",$e).")";}}foreach(driver()->checkConstraints($R)as$B=>$Wa)$n[]="CONSTRAINT ".idf_escape($B)." CHECK ($Wa)";return"CREATE TABLE ".table($R)." (\n\t".implode(",\n\t",$n)."\n)";}function
foreign_keys_sql($R){$n=array();foreach(foreign_keys($R)as$jd)$n[]=ltrim(format_foreign_key($jd));return($n?"ALTER TABLE ".table($R)." ADD\n\t".implode(",\n\t",$n).";\n\n":"");}function
truncate_sql($R){return"TRUNCATE TABLE ".table($R);}function
use_sql($Nb,$si=""){return"USE ".idf_escape($Nb);}function
trigger_sql($R){$J="";foreach(triggers($R)as$B=>$hj)$J
.=create_trigger(" ON ".table($R),trigger($B,$R)).";";return$J;}function
convert_field($m){}function
unconvert_field($m,$J){return$J;}function
support($Uc){return
preg_match('~^(check|comment|columns|database|drop_col|dump|indexes|descidx|scheme|sql|table|trigger|view|view_trigger)$~',$Uc);}}class
Adminer{static$instance;var$error='';function
name(){return"<a href='https://www.adminer.org/'".target_blank()." id='h1'><img src='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."' width='24' height='24' alt='' id='logo'>Adminer</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($h=false){return
password_file($h);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($N){return
h($N);}function
database(){return
DB;}function
databases($hd=true){return
get_databases($hd);}function
pluginsLinks(){}function
operators(){return
driver()->operators;}function
schemas(){return
schemas();}function
queryTimeout(){return
2;}function
afterConnect(){}function
headers(){}function
csp(array$Gb){return$Gb;}function
head($Kb=null){return
true;}function
bodyClass(){echo" adminer";}function
css(){$J=array();foreach(array("","-dark")as$tf){$o="adminer$tf.css";if(file_exists($o)){$Zc=file_get_contents($o);$J["$o?v=".crc32($Zc)]=($tf?"dark":(preg_match('~prefers-color-scheme:\s*dark~',$Zc)?'':'light'));}}return$J;}function
loginForm(){echo"<table class='layout'>\n",adminer()->loginFormField('driver','<tr><th>'.'System'.'<td>',html_select("auth[driver]",SqlDriver::$drivers,DRIVER,"loginDriver(this);")),adminer()->loginFormField('server','<tr><th>'.'Server'.'<td>','<input name="auth[server]" value="'.h(SERVER).'" title="hostname[:port]" placeholder="localhost" autocapitalize="off">'),adminer()->loginFormField('username','<tr><th>'.'Username'.'<td>','<input name="auth[username]" id="username" autofocus value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("const authDriver = qs('#username').form['auth[driver]']; authDriver && authDriver.onchange();")),adminer()->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'),adminer()->loginFormField('db','<tr><th>'.'Database'.'<td>','<input name="auth[db]" value="'.h($_GET["db"]).'" autocapitalize="off">'),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($B,$Hd,$Y){return$Hd.$Y."\n";}function
login($Te,$F){if($F=="")return
sprintf('Adminer does not support accessing a database without a password, <a href="https://www.adminer.org/en/password/"%s>more information</a>.',target_blank());return
true;}function
tableName(array$yi){return
h($yi["Name"]);}function
fieldName(array$m,$dg=0){$U=$m["full_type"];$ob=$m["comment"];return'<span title="'.h($U.($ob!=""?($U?": ":"").$ob:'')).'">'.h($m["field"]).'</span>';}function
selectLinks(array$yi,$O=""){$B=$yi["Name"];echo'<p class="links">';$Re=array("select"=>'Select data');if(support("table")||support("indexes"))$Re["table"]='Show structure';$ye=false;if(support("table")){$ye=is_view($yi);if(!$ye)$Re["create"]='Alter table';elseif(support("view"))$Re["view"]='Alter view';}if($O!==null)$Re["edit"]='New item';foreach($Re
as$x=>$X)echo" <a href='".h(ME)."$x=".urlencode($B).($x=="edit"?$O:"")."'".bold(isset($_GET[$x])).">$X</a>";echo
doc_link(array(JUSH=>driver()->tableHelp($B,$ye)),"?"),"\n";}function
foreignKeys($R){return
foreign_keys($R);}function
backwardKeys($R,$xi){return
array();}function
backwardKeysPrint(array$Da,array$K){}function
selectQuery($H,$oi,$Sc=false){$J="</p>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings";$J=", <a href='#$t'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."$J<div id='$t' class='hidden'>\n$Qj</div>\n";}return"<p><code class='jush-".JUSH."'>".h(str_replace("\n"," ",$H))."</code> <span class='time'>(".format_time($oi).")</span>".(support("sql")?" <a href='".h(ME)."sql=".urlencode($H)."'>".'Edit'."</a>":"").$J;}function
sqlCommandQuery($H){return
shorten_utf8(trim($H),1000);}function
sqlPrintAfter(){}function
rowDescription($R){return"";}function
rowDescriptions(array$L,array$kd){return$L;}function
selectLink($X,array$m){}function
selectVal($X,$_,array$m,$ng){$J=($X===null?"<i>NULL</i>":(preg_match("~char|binary|boolean~",$m["type"])&&!preg_match("~var~",$m["type"])?"<code>$X</code>":(preg_match('~json~',$m["type"])?"<code class='jush-js'>$X</code>":$X)));if(is_blob($m)&&!is_utf8($X))$J="<i>".lang_format(array('%d byte','%d bytes'),strlen($ng))."</i>";return($_?"<a href='".h($_)."'".(is_url($_)?target_blank():"").">$J</a>":$J);}function
editVal($X,array$m){return$X;}function
config(){return
array();}function
tableStructurePrint(array$n,$yi=null){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr><th>".'Column'."<td>".'Type'.(support("comment")?"<td>".'Comment':"")."</thead>\n";$ri=driver()->structuredTypes();foreach($n
as$m){echo"<tr><th>".h($m["field"]);$U=h($m["full_type"]);$c=h($m["collation"]);echo"<td><span title='$c'>".(in_array($U,(array)$ri['User types'])?"<a href='".h(ME.'type='.urlencode($U))."'>$U</a>":$U.($c&&isset($yi["Collation"])&&$c!=$yi["Collation"]?" $c":""))."</span>",($m["null"]?" <i>NULL</i>":""),($m["auto_increment"]?" <i>".'Auto Increment'."</i>":"");$k=h($m["default"]);echo(isset($m["default"])?" <span title='".'Default value'."'>[<b>".($m["generated"]?"<code class='jush-".JUSH."'>$k</code>":$k)."</b>]</span>":""),(support("comment")?"<td>".h($m["comment"]):""),"\n";}echo"</table>\n","</div>\n";}function
tableIndexesPrint(array$w,array$yi){$yg=false;foreach($w
as$B=>$v)$yg|=!!$v["partial"];echo"<table>\n";$Sb=first(driver()->indexAlgorithms($yi));foreach($w
as$B=>$v){ksort($v["columns"]);$Wg=array();foreach($v["columns"]as$x=>$X)$Wg[]="<i>".h($X)."</i>".($v["lengths"][$x]?"(".$v["lengths"][$x].")":"").($v["descs"][$x]?" DESC":"");echo"<tr title='".h($B)."'>","<th>$v[type]".($Sb&&$v['algorithm']!=$Sb?" ($v[algorithm])":""),"<td>".implode(", ",$Wg);if($yg)echo"<td>".($v['partial']?"<code class='jush-".JUSH."'>WHERE ".h($v['partial']):"");echo"\n";}echo"</table>\n";}function
selectColumnsPrint(array$M,array$e){print_fieldset("select",'Select',$M);$s=0;$M[""]=array();foreach($M
as$x=>$X){$X=idx($_GET["columns"],$x,array());$d=select_input(" name='columns[$s][col]'",$e,$X["col"],($x!==""?"selectFieldChange":"selectAddRow"));echo"<div>".(driver()->functions||driver()->grouping?html_select("columns[$s][fun]",array(-1=>"")+array_filter(array('Functions'=>driver()->functions,'Aggregation'=>driver()->grouping)),$X["fun"]).on_help("event.target.value && event.target.value.replace(/ |\$/, '(') + ')'",1).script("qsl('select').onchange = function () { helpClose();".($x!==""?"":" qsl('select, input', this.parentNode).onchange();")." };","")."($d)":$d)."</div>\n";$s++;}echo"</div></fieldset>\n";}function
selectSearchPrint(array$Z,array$e,array$w){print_fieldset("search",'Search',$Z);foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT")echo"<div>(<i>".implode("</i>, <i>",array_map('Adminer\h',$v["columns"]))."</i>) AGAINST"," <input type='search' name='fulltext[$s]' value='".h(idx($_GET["fulltext"],$s))."'>",script("qsl('input').oninput = selectFieldChange;",""),checkbox("boolean[$s]",1,isset($_GET["boolean"][$s]),"BOOL"),"</div>\n";}$Ta="this.parentNode.firstChild.onchange();";foreach(array_merge((array)$_GET["where"],array(array()))as$s=>$X){if(!$X||("$X[col]$X[val]"!=""&&in_array($X["op"],adminer()->operators())))echo"<div>".select_input(" name='where[$s][col]'",$e,$X["col"],($X?"selectFieldChange":"selectAddRow"),"(".'anywhere'.")"),html_select("where[$s][op]",adminer()->operators(),$X["op"],$Ta),"<input type='search' name='where[$s][val]' value='".h($X["val"])."'>",script("mixin(qsl('input'), {oninput: function () { $Ta }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});",""),"</div>\n";}echo"</div></fieldset>\n";}function
selectOrderPrint(array$dg,array$e,array$w){print_fieldset("sort",'Sort',$dg);$s=0;foreach((array)$_GET["order"]as$x=>$X){if($X!=""){echo"<div>".select_input(" name='order[$s]'",$e,$X,"selectFieldChange"),checkbox("desc[$s]",1,isset($_GET["desc"][$x]),'descending')."</div>\n";$s++;}}echo"<div>".select_input(" name='order[$s]'",$e,"","selectAddRow"),checkbox("desc[$s]",1,false,'descending')."</div>\n","</div></fieldset>\n";}function
selectLimitPrint($z){echo"<fieldset><legend>".'Limit'."</legend><div>","<input type='number' name='limit' class='size' value='".intval($z)."'>",script("qsl('input').oninput = selectFieldChange;",""),"</div></fieldset>\n";}function
selectLengthPrint($Oi){if($Oi!==null)echo"<fieldset><legend>".'Text length'."</legend><div>","<input type='number' name='text_length' class='size' value='".h($Oi)."'>","</div></fieldset>\n";}function
selectActionPrint(array$w){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>"," <span id='noindex' title='".'Full table scan'."'></span>","<script".nonce().">\n","const indexColumns = ";$e=array();foreach($w
as$v){$Jb=reset($v["columns"]);if($v["type"]!="FULLTEXT"&&$Jb)$e[$Jb]=1;}$e[""]=1;foreach($e
as$x=>$X)json_row($x);echo";\n","selectFieldChange.call(qs('#form')['select']);\n","</script>\n","</div></fieldset>\n";}function
selectCommandPrint(){return!information_schema(DB);}function
selectImportPrint(){return!information_schema(DB);}function
selectEmailPrint(array$uc,array$e){}function
selectColumnsProcess(array$e,array$w){$M=array();$wd=array();foreach((array)$_GET["columns"]as$x=>$X){if($X["fun"]=="count"||($X["col"]!=""&&(!$X["fun"]||in_array($X["fun"],driver()->functions)||in_array($X["fun"],driver()->grouping)))){$M[$x]=apply_sql_function($X["fun"],($X["col"]!=""?idf_escape($X["col"]):"*"));if(!in_array($X["fun"],driver()->grouping))$wd[]=$M[$x];}}return
array($M,$wd);}function
selectSearchProcess(array$n,array$w){$J=array();foreach($w
as$s=>$v){if($v["type"]=="FULLTEXT"&&idx($_GET["fulltext"],$s)!="")$J[]="MATCH (".implode(", ",array_map('Adminer\idf_escape',$v["columns"])).") AGAINST (".q($_GET["fulltext"][$s]).(isset($_GET["boolean"][$s])?" IN BOOLEAN MODE":"").")";}foreach((array)$_GET["where"]as$x=>$X){$hb=$X["col"];if("$hb$X[val]"!=""&&in_array($X["op"],adminer()->operators())){$sb=array();foreach(($hb!=""?array($hb=>$n[$hb]):$n)as$B=>$m){$Sg="";$rb=" $X[op]";if(preg_match('~IN$~',$X["op"])){$Wd=process_length($X["val"]);$rb
.=" ".($Wd!=""?$Wd:"(NULL)");}elseif($X["op"]=="SQL")$rb=" $X[val]";elseif(preg_match('~^(I?LIKE) %%$~',$X["op"],$A))$rb=" $A[1] ".adminer()->processInput($m,"%$X[val]%");elseif($X["op"]=="FIND_IN_SET"){$Sg="$X[op](".q($X["val"]).", ";$rb=")";}elseif(!preg_match('~NULL$~',$X["op"]))$rb
.=" ".adminer()->processInput($m,$X["val"]);if($hb!=""||(isset($m["privileges"]["where"])&&(preg_match('~^[-\d.'.(preg_match('~IN$~',$X["op"])?',':'').']+$~',$X["val"])||!preg_match('~'.number_type().'|bit~',$m["type"]))&&(!preg_match("~[\x80-\xFF]~",$X["val"])||preg_match('~char|text|enum|set~',$m["type"]))&&(!preg_match('~date|timestamp~',$m["type"])||preg_match('~^\d+-\d+-\d+~',$X["val"]))))$sb[]=$Sg.driver()->convertSearch(idf_escape($B),$X,$m).$rb;}$J[]=(count($sb)==1?$sb[0]:($sb?"(".implode(" OR ",$sb).")":"1 = 0"));}}return$J;}function
selectOrderProcess(array$n,array$w){$J=array();foreach((array)$_GET["order"]as$x=>$X){if($X!="")$J[]=(preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~',$X)?$X:idf_escape($X)).(isset($_GET["desc"][$x])?" DESC":"");}return$J;}function
selectLimitProcess(){return(isset($_GET["limit"])?intval($_GET["limit"]):50);}function
selectLengthProcess(){return(isset($_GET["text_length"])?"$_GET[text_length]":"100");}function
selectEmailProcess(array$Z,array$kd){return
false;}function
selectQueryBuild(array$M,array$Z,array$wd,array$dg,$z,$D){return"";}function
messageQuery($H,$Pi,$Sc=false){restart_session();$Jd=&get_session("queries");if(!idx($Jd,$_GET["db"]))$Jd[$_GET["db"]]=array();if(strlen($H)>1e6)$H=preg_replace('~[\x80-\xFF]+$~','',substr($H,0,1e6))."\n‚Ä¶";$Jd[$_GET["db"]][]=array($H,time(),$Pi);$ki="sql-".count($Jd[$_GET["db"]]);$J="<a href='#$ki' class='toggle'>".'SQL command'."</a> <a href='' class='jsonly copy'>üóê</a>\n";if(!$Sc&&($Qj=driver()->warnings())){$t="warnings-".count($Jd[$_GET["db"]]);$J="<a href='#$t' class='toggle'>".'Warnings'."</a>, $J<div id='$t' class='hidden'>\n$Qj</div>\n";}return" <span class='time'>".@date("H:i:s")."</span>"." $J<div id='$ki' class='hidden'><pre><code class='jush-".JUSH."'>".shorten_utf8($H,1000)."</code></pre>".($Pi?" <span class='time'>($Pi)</span>":'').(support("sql")?'<p><a href="'.h(str_replace("db=".urlencode(DB),"db=".urlencode($_GET["db"]),ME).'sql=&history='.(count($Jd[$_GET["db"]])-1)).'">'.'Edit'.'</a>':'').'</div>';}function
editRowPrint($R,array$n,$K,$wj){}function
editFunctions(array$m){$J=($m["null"]?"NULL/":"");$wj=isset($_GET["select"])||where($_GET);foreach(array(driver()->insertFunctions,driver()->editFunctions)as$x=>$rd){if(!$x||(!isset($_GET["call"])&&$wj)){foreach($rd
as$Gg=>$X){if(!$Gg||preg_match("~$Gg~",$m["type"]))$J
.="/$X";}}if($x&&$rd&&!preg_match('~set|bool~',$m["type"])&&!is_blob($m))$J
.="/SQL";}if($m["auto_increment"]&&!$wj)$J='Auto Increment';return
explode("/",$J);}function
editInput($R,array$m,$ya,$Y){if($m["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$ya value='orig' checked><i>".'original'."</i></label> ":"").enum_input("radio",$ya,$m,$Y,"NULL");return"";}function
editHint($R,array$m,$Y){return"";}function
processInput(array$m,$Y,$r=""){if($r=="SQL")return$Y;$B=$m["field"];$J=q($Y);if(preg_match('~^(now|getdate|uuid)$~',$r))$J="$r()";elseif(preg_match('~^current_(date|timestamp)$~',$r))$J=$r;elseif(preg_match('~^([+-]|\|\|)$~',$r))$J=idf_escape($B)." $r $J";elseif(preg_match('~^[+-] interval$~',$r))$J=idf_escape($B)." $r ".(preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i",$Y)&&JUSH!="pgsql"?$Y:$J);elseif(preg_match('~^(addtime|subtime|concat)$~',$r))$J="$r(".idf_escape($B).", $J)";elseif(preg_match('~^(md5|sha1|password|encrypt)$~',$r))$J="$r($J)";return
unconvert_field($m,$J);}function
dumpOutput(){$J=array('text'=>'open','file'=>'save');if(function_exists('gzencode'))$J['gz']='gzip';return$J;}function
dumpFormat(){return(support("dump")?array('sql'=>'SQL'):array())+array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($j){}function
dumpTable($R,$si,$ye=0){if($_POST["format"]!="sql"){echo"\xef\xbb\xbf";if($si)dump_csv(array_keys(fields($R)));}else{if($ye==2){$n=array();foreach(fields($R)as$B=>$m)$n[]=idf_escape($B)." $m[full_type]";$h="CREATE TABLE ".table($R)." (".implode(", ",$n).")";}else$h=create_sql($R,$_POST["auto_increment"],$si);set_utf8mb4($h);if($si&&$h){if($si=="DROP+CREATE"||$ye==1)echo"DROP ".($ye==2?"VIEW":"TABLE")." IF EXISTS ".table($R).";\n";if($ye==1)$h=remove_definer($h);echo"$h;\n\n";}}}function
dumpData($R,$si,$H){if($si){$df=(JUSH=="sqlite"?0:1048576);$n=array();$Sd=false;if($_POST["format"]=="sql"){if($si=="TRUNCATE+INSERT")echo
truncate_sql($R).";\n";$n=fields($R);if(JUSH=="mssql"){foreach($n
as$m){if($m["auto_increment"]){echo"SET IDENTITY_INSERT ".table($R)." ON;\n";$Sd=true;break;}}}}$I=connection()->query($H,1);if($I){$ne="";$Na="";$Ce=array();$sd=array();$ui="";$Vc=($R!=''?'fetch_assoc':'fetch_row');$Cb=0;while($K=$I->$Vc()){if(!$Ce){$Ij=array();foreach($K
as$X){$m=$I->fetch_field();if(idx($n[$m->name],'generated')){$sd[$m->name]=true;continue;}$Ce[]=$m->name;$x=idf_escape($m->name);$Ij[]="$x = VALUES($x)";}$ui=($si=="INSERT+UPDATE"?"\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij):"").";\n";}if($_POST["format"]!="sql"){if($si=="table"){dump_csv($Ce);$si="INSERT";}dump_csv($K);}else{if(!$ne)$ne="INSERT INTO ".table($R)." (".implode(", ",array_map('Adminer\idf_escape',$Ce)).") VALUES";foreach($K
as$x=>$X){if($sd[$x]){unset($K[$x]);continue;}$m=$n[$x];$K[$x]=($X!==null?unconvert_field($m,preg_match(number_type(),$m["type"])&&!preg_match('~\[~',$m["full_type"])&&is_numeric($X)?$X:q(($X===false?0:$X))):"NULL");}$Dh=($df?"\n":" ")."(".implode(",\t",$K).")";if(!$Na)$Na=$ne.$Dh;elseif(JUSH=='mssql'?$Cb%1000!=0:strlen($Na)+4+strlen($Dh)+strlen($ui)<$df)$Na
.=",$Dh";else{echo$Na.$ui;$Na=$ne.$Dh;}}$Cb++;}if($Na)echo$Na.$ui;}elseif($_POST["format"]=="sql")echo"-- ".str_replace("\n"," ",connection()->error)."\n";if($Sd)echo"SET IDENTITY_INSERT ".table($R)." OFF;\n";}}function
dumpFilename($Qd){return
friendly_url($Qd!=""?$Qd:(SERVER?:"localhost"));}function
dumpHeaders($Qd,$wf=false){$qg=$_POST["output"];$Nc=(preg_match('~sql~',$_POST["format"])?"sql":($wf?"tar":"csv"));header("Content-Type: ".($qg=="gz"?"application/x-gzip":($Nc=="tar"?"application/x-tar":($Nc=="sql"||$qg!="file"?"text/plain":"text/csv")."; charset=utf-8")));if($qg=="gz"){ob_start(function($Q){return
gzencode($Q);},1e6);}return$Nc;}function
dumpFooter(){if($_POST["format"]=="sql")echo"-- ".gmdate("Y-m-d H:i:s e")."\n";}function
importServerPath(){return"adminer.sql";}function
homepage(){echo'<p class="links">'.($_GET["ns"]==""&&support("database")?'<a href="'.h(ME).'database=">'.'Alter database'."</a>\n":""),(support("scheme")?"<a href='".h(ME)."scheme='>".($_GET["ns"]!=""?'Alter schema':'Create schema')."</a>\n":""),($_GET["ns"]!==""?'<a href="'.h(ME).'schema=">'.'Database schema'."</a>\n":""),(support("privileges")?"<a href='".h(ME)."privileges='>".'Privileges'."</a>\n":"");if($_GET["ns"]!=="")echo(support("routine")?"<a href='#routines'>".'Routines'."</a>\n":""),(support("sequence")?"<a href='#sequences'>".'Sequences'."</a>\n":""),(support("type")?"<a href='#user-types'>".'User types'."</a>\n":""),(support("event")?"<a href='#events'>".'Events'."</a>\n":"");return
true;}function
navigation($sf){echo"<h1>".adminer()->name()." <span class='version'>".VERSION;$Df=$_COOKIE["adminer_version"];echo" <a href='https://www.adminer.org/#download'".target_blank()." id='version'>".(version_compare(VERSION,$Df)<0?h($Df):"")."</a>","</span></h1>\n";if($sf=="auth"){$qg="";foreach((array)$_SESSION["pwds"]as$Kj=>$Wh){foreach($Wh
as$N=>$Fj){$B=h(get_setting("vendor-$Kj-$N")?:get_driver($Kj));foreach($Fj
as$V=>$F){if($F!==null){$Qb=$_SESSION["db"][$Kj][$N][$V];foreach(($Qb?array_keys($Qb):array(""))as$j)$qg
.="<li><a href='".h(auth_url($Kj,$N,$V,$j))."'>($B) ".h("$V@".($N!=""?adminer()->serverName($N):"").($j!=""?" - $j":""))."</a>\n";}}}}if($qg)echo"<ul id='logins'>\n$qg</ul>\n".script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");}else{$T=array();if($_GET["ns"]!==""&&!$sf&&DB!=""){connection()->select_db(DB);$T=table_status('',true);}adminer()->syntaxHighlighting($T);adminer()->databasesPrint($sf);$ia=array();if(DB==""||!$sf){if(support("sql")){$ia[]="<a href='".h(ME)."sql='".bold(isset($_GET["sql"])&&!isset($_GET["import"])).">".'SQL command'."</a>";$ia[]="<a href='".h(ME)."import='".bold(isset($_GET["import"])).">".'Import'."</a>";}$ia[]="<a href='".h(ME)."dump=".urlencode(isset($_GET["table"])?$_GET["table"]:$_GET["select"])."' id='dump'".bold(isset($_GET["dump"])).">".'Export'."</a>";}$Xd=$_GET["ns"]!==""&&!$sf&&DB!="";if($Xd)$ia[]='<a href="'.h(ME).'create="'.bold($_GET["create"]==="").">".'Create table'."</a>";echo($ia?"<p class='links'>\n".implode("\n",$ia)."\n":"");if($Xd){if($T)adminer()->tablesPrint($T);else
echo"<p class='message'>".'No tables.'."</p>\n";}}}function
syntaxHighlighting(array$T){echo
script_src(preg_replace("~\\?.*~","",ME)."?file=jush.js&version=5.4.1",true);if(support("sql")){echo"<script".nonce().">\n";if($T){$Re=array();foreach($T
as$R=>$U)$Re[]=preg_quote($R,'/');echo"var jushLinks = { ".JUSH.":";json_row(js_escape(ME).(support("table")?"table":"select").'=$&','/\b('.implode('|',$Re).')\b/g',false);if(support('routine')){foreach(routines()as$K)json_row(js_escape(ME).'function='.urlencode($K["SPECIFIC_NAME"]).'&name=$&','/\b'.preg_quote($K["ROUTINE_NAME"],'/').'(?=["`]?\()/g',false);}json_row('');echo"};\n";foreach(array("bac","bra","sqlite_quo","mssql_bra")as$X)echo"jushLinks.$X = jushLinks.".JUSH.";\n";if(isset($_GET["sql"])||isset($_GET["trigger"])||isset($_GET["check"])){$Ei=array_fill_keys(array_keys($T),array());foreach(driver()->allFields()as$R=>$n){foreach($n
as$m)$Ei[$R][]=$m["field"];}echo"addEventListener('DOMContentLoaded', () => { autocompleter = jush.autocompleteSql('".idf_escape("")."', ".json_encode($Ei)."); });\n";}}echo"</script>\n";}echo
script("syntaxHighlighting('".preg_replace('~^(\d\.?\d).*~s','\1',connection()->server_info)."', '".connection()->flavor."');");}function
databasesPrint($sf){$i=adminer()->databases();if(DB&&$i&&!in_array(DB,$i))array_unshift($i,DB);echo"<form action=''>\n<p id='dbs'>\n";hidden_fields_get();$Ob=script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");echo"<label title='".'Database'."'>".'DB'.": ".($i?html_select("db",array(""=>"")+$i,DB).$Ob:"<input name='db' value='".h(DB)."' autocapitalize='off' size='19'>\n")."</label>","<input type='submit' value='".'Use'."'".($i?" class='hidden'":"").">\n";if(support("scheme")){if($sf!="db"&&DB!=""&&connection()->select_db(DB)){echo"<br><label>".'Schema'.": ".html_select("ns",array(""=>"")+adminer()->schemas(),$_GET["ns"])."$Ob</label>";if($_GET["ns"]!="")set_schema($_GET["ns"]);}}foreach(array("import","sql","schema","dump","privileges")as$X){if(isset($_GET[$X])){echo
input_hidden($X);break;}}echo"</p></form>\n";}function
tablesPrint(array$T){echo"<ul id='tables'>".script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($T
as$R=>$P){$R="$R";$B=adminer()->tableName($P);if($B!=""&&!$P["partition"])echo'<li><a href="'.h(ME).'select='.urlencode($R).'"'.bold($_GET["select"]==$R||$_GET["edit"]==$R,"select")." title='".'Select data'."'>".'select'."</a> ",(support("table")||support("indexes")?'<a href="'.h(ME).'table='.urlencode($R).'"'.bold(in_array($R,array($_GET["table"],$_GET["create"],$_GET["indexes"],$_GET["foreign"],$_GET["trigger"],$_GET["check"],$_GET["view"])),(is_view($P)?"view":"structure"))." title='".'Show structure'."'>$B</a>":"<span>$B</span>")."\n";}echo"</ul>\n";}function
processList(){return
process_list();}function
killProcess($t){return
kill_process($t);}}class
Plugins{private
static$append=array('dumpFormat'=>true,'dumpOutput'=>true,'editRowPrint'=>true,'editFunctions'=>true,'config'=>true);var$plugins;var$error='';private$hooks=array();function
__construct($Lg){if($Lg===null){$Lg=array();$Ha="adminer-plugins";if(is_dir($Ha)){foreach(glob("$Ha/*.php")as$o)$Yd=include_once"./$o";}$Id=" href='https://www.adminer.org/plugins/#use'".target_blank();if(file_exists("$Ha.php")){$Yd=include_once"./$Ha.php";if(is_array($Yd)){foreach($Yd
as$Kg)$Lg[get_class($Kg)]=$Kg;}else$this->error
.=sprintf('%s must <a%s>return an array</a>.',"<b>$Ha.php</b>",$Id)."<br>";}foreach(get_declared_classes()as$db){if(!$Lg[$db]&&preg_match('~^Adminer\w~i',$db)){$oh=new
\ReflectionClass($db);$xb=$oh->getConstructor();if($xb&&$xb->getNumberOfRequiredParameters())$this->error
.=sprintf('<a%s>Configure</a> %s in %s.',$Id,"<b>$db</b>","<b>$Ha.php</b>")."<br>";else$Lg[$db]=new$db;}}}$this->plugins=$Lg;$la=new
Adminer;$Lg[]=$la;$oh=new
\ReflectionObject($la);foreach($oh->getMethods()as$qf){foreach($Lg
as$Kg){$B=$qf->getName();if(method_exists($Kg,$B))$this->hooks[$B][]=$Kg;}}}function
__call($B,array$vg){$ua=array();foreach($vg
as$x=>$X)$ua[]=&$vg[$x];$J=null;foreach($this->hooks[$B]as$Kg){$Y=call_user_func_array(array($Kg,$B),$ua);if($Y!==null){if(!self::$append[$B])return$Y;$J=$Y+(array)$J;}}return$J;}}abstract
class
Plugin{protected$translations=array();function
description(){return$this->lang('');}function
screenshot(){return"";}protected
function
lang($u,$Jf=null){$ua=func_get_args();$ua[0]=idx($this->translations[LANG],$u)?:$u;return
call_user_func_array('Adminer\lang_format',$ua);}}Adminer::$instance=(function_exists('adminer_object')?adminer_object():(is_dir("adminer-plugins")||file_exists("adminer-plugins.php")?new
Plugins(null):new
Adminer));SqlDriver::$drivers=array("server"=>"MySQL / MariaDB")+SqlDriver::$drivers;if(!defined('Adminer\DRIVER')){define('Adminer\DRIVER',"server");if(extension_loaded("mysqli")&&$_GET["ext"]!="pdo"){class
Db
extends
\MySQLi{static$instance;var$extension="MySQLi",$flavor='';function
__construct(){parent::init();}function
attach($N,$V,$F){mysqli_report(MYSQLI_REPORT_OFF);list($Md,$Mg)=host_port($N);$ni=adminer()->connectSsl();if($ni)$this->ssl_set($ni['key'],$ni['cert'],$ni['ca'],'','');$J=@$this->real_connect(($N!=""?$Md:ini_get("mysqli.default_host")),($N.$V!=""?$V:ini_get("mysqli.default_user")),($N.$V.$F!=""?$F:ini_get("mysqli.default_pw")),null,(is_numeric($Mg)?intval($Mg):ini_get("mysqli.default_port")),(is_numeric($Mg)?null:$Mg),($ni?($ni['verify']!==false?2048:64):0));$this->options(MYSQLI_OPT_LOCAL_INFILE,0);return($J?'':$this->error);}function
set_charset($Va){if(parent::set_charset($Va))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Va");}function
next_result(){return
self::more_results()&&parent::next_result();}function
quote($Q){return"'".$this->escape_string($Q)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Db
extends
SqlDb{private$link;function
attach($N,$V,$F){if(ini_bool("mysql.allow_local_infile"))return
sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");$this->link=@mysql_connect(($N!=""?$N:ini_get("mysql.default_host")),($N.$V!=""?$V:ini_get("mysql.default_user")),($N.$V.$F!=""?$F:ini_get("mysql.default_password")),true,131072);if(!$this->link)return
mysql_error();$this->server_info=mysql_get_server_info($this->link);return'';}function
set_charset($Va){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Va,$this->link))return
true;mysql_set_charset('utf8',$this->link);}return$this->query("SET NAMES $Va");}function
quote($Q){return"'".mysql_real_escape_string($Q,$this->link)."'";}function
select_db($Nb){return
mysql_select_db($Nb,$this->link);}function
query($H,$oj=false){$I=@($oj?mysql_unbuffered_query($H,$this->link):mysql_query($H,$this->link));$this->error="";if(!$I){$this->errno=mysql_errno($this->link);$this->error=mysql_error($this->link);return
false;}if($I===true){$this->affected_rows=mysql_affected_rows($this->link);$this->info=mysql_info($this->link);return
true;}return
new
Result($I);}}class
Result{var$num_rows;private$result;private$offset=0;function
__construct($I){$this->result=$I;$this->num_rows=mysql_num_rows($I);}function
fetch_assoc(){return
mysql_fetch_assoc($this->result);}function
fetch_row(){return
mysql_fetch_row($this->result);}function
fetch_field(){$J=mysql_fetch_field($this->result,$this->offset++);$J->orgtable=$J->table;$J->charsetnr=($J->blob?63:0);return$J;}function
__destruct(){mysql_free_result($this->result);}}}elseif(extension_loaded("pdo_mysql")){class
Db
extends
PdoDb{var$extension="PDO_MySQL";function
attach($N,$V,$F){$bg=array(\PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$ni=adminer()->connectSsl();if($ni){if($ni['key'])$bg[\PDO::MYSQL_ATTR_SSL_KEY]=$ni['key'];if($ni['cert'])$bg[\PDO::MYSQL_ATTR_SSL_CERT]=$ni['cert'];if($ni['ca'])$bg[\PDO::MYSQL_ATTR_SSL_CA]=$ni['ca'];if(isset($ni['verify']))$bg[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=$ni['verify'];}list($Md,$Mg)=host_port($N);return$this->dsn("mysql:charset=utf8;host=$Md".($Mg?(is_numeric($Mg)?";port=":";unix_socket=").$Mg:""),$V,$F,$bg);}function
set_charset($Va){return$this->query("SET NAMES $Va");}function
select_db($Nb){return$this->query("USE ".idf_escape($Nb));}function
query($H,$oj=false){$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$oj);return
parent::query($H,$oj);}}}class
Driver
extends
SqlDriver{static$extensions=array("MySQLi","MySQL","PDO_MySQL");static$jush="sql";var$unsigned=array("unsigned","zerofill","unsigned zerofill");var$operators=array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL");var$functions=array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper");var$grouping=array("avg","count","count distinct","group_concat","max","min","sum");static
function
connect($N,$V,$F){$f=parent::connect($N,$V,$F);if(is_string($f)){if(function_exists('iconv')&&!is_utf8($f)&&strlen($Dh=iconv("windows-1250","utf-8",$f))>strlen($f))$f=$Dh;return$f;}$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");$f->flavor=(preg_match('~MariaDB~',$f->server_info)?'maria':'mysql');add_driver(DRIVER,($f->flavor=='maria'?"MariaDB":"MySQL"));return$f;}function
__construct(Db$f){parent::__construct($f);$this->types=array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),);$this->insertFunctions=array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",);$this->editFunctions=array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",);if(min_version('5.7.8',10.2,$f))$this->types['Strings']["json"]=4294967295;if(min_version('',10.7,$f)){$this->types['Strings']["uuid"]=128;$this->insertFunctions['uuid']='uuid';}if(min_version(9,'',$f)){$this->types['Numbers']["vector"]=16383;$this->insertFunctions['vector']='string_to_vector';}if(min_version(5.1,'',$f))$this->partitionBy=array("HASH","LINEAR HASH","KEY","LINEAR KEY","RANGE","LIST");if(min_version(5.7,10.2,$f))$this->generated=array("STORED","VIRTUAL");}function
unconvertFunction(array$m){return(preg_match("~binary~",$m["type"])?"<code class='jush-sql'>UNHEX</code>":($m["type"]=="bit"?doc_link(array('sql'=>'bit-value-literals.html'),"<code>b''</code>"):(preg_match("~geometry|point|linestring|polygon~",$m["type"])?"<code class='jush-sql'>GeomFromText</code>":"")));}function
insert($R,array$O){return($O?parent::insert($R,$O):queries("INSERT INTO ".table($R)." ()\nVALUES ()"));}function
insertUpdate($R,array$L,array$G){$e=array_keys(reset($L));$Sg="INSERT INTO ".table($R)." (".implode(", ",$e).") VALUES\n";$Ij=array();foreach($e
as$x)$Ij[$x]="$x = VALUES($x)";$ui="\nON DUPLICATE KEY UPDATE ".implode(", ",$Ij);$Ij=array();$y=0;foreach($L
as$O){$Y="(".implode(", ",$O).")";if($Ij&&(strlen($Sg)+$y+strlen($Y)+strlen($ui)>1e6)){if(!queries($Sg.implode(",\n",$Ij).$ui))return
false;$Ij=array();$y=0;}$Ij[]=$Y;$y+=strlen($Y)+2;}return
queries($Sg.implode(",\n",$Ij).$ui);}function
slowQuery($H,$Qi){if(min_version('5.7.8','10.1.2')){if($this->conn->flavor=='maria')return"SET STATEMENT max_statement_time=$Qi FOR $H";elseif(preg_match('~^(SELECT\b)(.+)~is',$H,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($Qi*1000).") */ $A[2]";}}function
convertSearch($u,array$X,array$m){return(preg_match('~char|text|enum|set~',$m["type"])&&!preg_match("~^utf8~",$m["collation"])&&preg_match('~[\x80-\xFF]~',$X['val'])?"CONVERT($u USING ".charset($this->conn).")":$u);}function
warnings(){$I=$this->conn->query("SHOW WARNINGS");if($I&&$I->num_rows){ob_start();print_select_result($I);return
ob_get_clean();}}function
tableHelp($B,$ye=false){$Ve=($this->conn->flavor=='maria');if(information_schema(DB))return
strtolower("information-schema-".($Ve?"$B-table/":str_replace("_","-",$B)."-table.html"));if(DB=="mysql")return($Ve?"mysql$B-table/":"system-schema.html");}function
partitionsInfo($R){$pd="FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = ".q(DB)." AND TABLE_NAME = ".q($R);$I=$this->conn->query("SELECT PARTITION_METHOD, PARTITION_EXPRESSION, PARTITION_ORDINAL_POSITION $pd ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");$J=array();list($J["partition_by"],$J["partition"],$J["partitions"])=$I->fetch_row();$Cg=get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $pd AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");$J["partition_names"]=array_keys($Cg);$J["partition_values"]=array_values($Cg);return$J;}function
hasCStyleEscapes(){static$Qa;if($Qa===null){$li=get_val("SHOW VARIABLES LIKE 'sql_mode'",1,$this->conn);$Qa=(strpos($li,'NO_BACKSLASH_ESCAPES')===false);}return$Qa;}function
engines(){$J=array();foreach(get_rows("SHOW ENGINES")as$K){if(preg_match("~YES|DEFAULT~",$K["Support"]))$J[]=$K["Engine"];}return$J;}function
indexAlgorithms(array$yi){return(preg_match('~^(MEMORY|NDB)$~',$yi["Engine"])?array("HASH","BTREE"):array());}}function
idf_escape($u){return"`".str_replace("`","``",$u)."`";}function
table($u){return
idf_escape($u);}function
get_databases($hd){$J=get_session("dbs");if($J===null){$H="SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME";$J=($hd?slow_query($H):get_vals($H));restart_session();set_session("dbs",$J);stop_session();}return$J;}function
limit($H,$Z,$z,$C=0,$Rh=" "){return" $H$Z".($z?$Rh."LIMIT $z".($C?" OFFSET $C":""):"");}function
limit1($R,$H,$Z,$Rh="\n"){return
limit($H,$Z,1,0,$Rh);}function
db_collation($j,array$jb){$J=null;$h=get_val("SHOW CREATE DATABASE ".idf_escape($j),1);if(preg_match('~ COLLATE ([^ ]+)~',$h,$A))$J=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$h,$A))$J=$jb[$A[1]][-1];return$J;}function
logged_user(){return
get_val("SELECT USER()");}function
tables_list(){return
get_key_vals("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");}function
count_tables(array$i){$J=array();foreach($i
as$j)$J[$j]=count(get_vals("SHOW TABLES IN ".idf_escape($j)));return$J;}function
table_status($B="",$Tc=false){$J=array();foreach(get_rows($Tc?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($B!=""?"AND TABLE_NAME = ".q($B):"ORDER BY Name"):"SHOW TABLE STATUS".($B!=""?" LIKE ".q(addcslashes($B,"%_\\")):""))as$K){if($K["Engine"]=="InnoDB")$K["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$K["Comment"]);if(!isset($K["Engine"]))$K["Comment"]="";if($B!="")$K["Name"]=$B;$J[$K["Name"]]=$K;}return$J;}function
is_view(array$S){return$S["Engine"]===null;}function
fk_support(array$S){return
preg_match('~InnoDB|IBMDB2I'.(min_version(5.6)?'|NDB':'').'~i',$S["Engine"]);}function
fields($R){$Ve=(connection()->flavor=='maria');$J=array();foreach(get_rows("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ".q($R)." ORDER BY ORDINAL_POSITION")as$K){$m=$K["COLUMN_NAME"];$U=$K["COLUMN_TYPE"];$td=$K["GENERATION_EXPRESSION"];$Qc=$K["EXTRA"];preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$Qc,$sd);preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$U,$Ye);$k=$K["COLUMN_DEFAULT"];if($k!=""){$xe=preg_match('~text|json~',$Ye[1]);if(!$Ve&&$xe)$k=preg_replace("~^(_\w+)?('.*')$~",'\2',stripslashes($k));if($Ve||$xe){$k=($k=="NULL"?null:preg_replace_callback("~^'(.*)'$~",function($A){return
stripslashes(str_replace("''","'",$A[1]));},$k));}if(!$Ve&&preg_match('~binary~',$Ye[1])&&preg_match('~^0x(\w*)$~',$k,$A))$k=pack("H*",$A[1]);}$J[$m]=array("field"=>$m,"full_type"=>$U,"type"=>$Ye[1],"length"=>$Ye[2],"unsigned"=>ltrim($Ye[3].$Ye[4]),"default"=>($sd?($Ve?$td:stripslashes($td)):$k),"null"=>($K["IS_NULLABLE"]=="YES"),"auto_increment"=>($Qc=="auto_increment"),"on_update"=>(preg_match('~\bon update (\w+)~i',$Qc,$A)?$A[1]:""),"collation"=>$K["COLLATION_NAME"],"privileges"=>array_flip(explode(",","$K[PRIVILEGES],where,order")),"comment"=>$K["COLUMN_COMMENT"],"primary"=>($K["COLUMN_KEY"]=="PRI"),"generated"=>($sd[1]=="PERSISTENT"?"STORED":$sd[1]),);}return$J;}function
indexes($R,$g=null){$J=array();foreach(get_rows("SHOW INDEX FROM ".table($R),$g)as$K){$B=$K["Key_name"];$J[$B]["type"]=($B=="PRIMARY"?"PRIMARY":($K["Index_type"]=="FULLTEXT"?"FULLTEXT":($K["Non_unique"]?($K["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$J[$B]["columns"][]=$K["Column_name"];$J[$B]["lengths"][]=($K["Index_type"]=="SPATIAL"?null:$K["Sub_part"]);$J[$B]["descs"][]=null;$J[$B]["algorithm"]=$K["Index_type"];}return$J;}function
foreign_keys($R){static$Gg='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$J=array();$Db=get_val("SHOW CREATE TABLE ".table($R),1);if($Db){preg_match_all("~CONSTRAINT ($Gg) FOREIGN KEY ?\\(((?:$Gg,? ?)+)\\) REFERENCES ($Gg)(?:\\.($Gg))? \\(((?:$Gg,? ?)+)\\)(?: ON DELETE (".driver()->onActions."))?(?: ON UPDATE (".driver()->onActions."))?~",$Db,$Ze,PREG_SET_ORDER);foreach($Ze
as$A){preg_match_all("~$Gg~",$A[2],$fi);preg_match_all("~$Gg~",$A[5],$Ii);$J[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('Adminer\idf_unescape',$fi[0]),"target"=>array_map('Adminer\idf_unescape',$Ii[0]),"on_delete"=>($A[6]?:"RESTRICT"),"on_update"=>($A[7]?:"RESTRICT"),);}}return$J;}function
view($B){return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',get_val("SHOW CREATE VIEW ".table($B),1)));}function
collations(){$J=array();foreach(get_rows("SHOW COLLATION")as$K){if($K["Default"])$J[$K["Charset"]][-1]=$K["Collation"];else$J[$K["Charset"]][]=$K["Collation"];}ksort($J);foreach($J
as$x=>$X)sort($J[$x]);return$J;}function
information_schema($j){return($j=="information_schema")||(min_version(5.5)&&$j=="performance_schema");}function
error(){return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",connection()->error));}function
create_database($j,$c){return
queries("CREATE DATABASE ".idf_escape($j).($c?" COLLATE ".q($c):""));}function
drop_databases(array$i){$J=apply_queries("DROP DATABASE",$i,'Adminer\idf_escape');restart_session();set_session("dbs",null);return$J;}function
rename_database($B,$c){$J=false;if(create_database($B,$c)){$T=array();$Nj=array();foreach(tables_list()as$R=>$U){if($U=='VIEW')$Nj[]=$R;else$T[]=$R;}$J=(!$T&&!$Nj)||move_tables($T,$Nj,$B);drop_databases($J?array(DB):array());}return$J;}function
auto_increment(){$Aa=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$v){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$v["columns"],true)){$Aa="";break;}if($v["type"]=="PRIMARY")$Aa=" UNIQUE";}}return" AUTO_INCREMENT$Aa";}function
alter_table($R,$B,array$n,array$jd,$ob,$xc,$c,$_a,$E){$b=array();foreach($n
as$m){if($m[1]){$k=$m[1][3];if(preg_match('~ GENERATED~',$k)){$m[1][3]=(connection()->flavor=='maria'?"":$m[1][2]);$m[1][2]=$k;}$b[]=($R!=""?($m[0]!=""?"CHANGE ".idf_escape($m[0]):"ADD"):" ")." ".implode($m[1]).($R!=""?$m[2]:"");}else$b[]="DROP ".idf_escape($m[0]);}$b=array_merge($b,$jd);$P=($ob!==null?" COMMENT=".q($ob):"").($xc?" ENGINE=".q($xc):"").($c?" COLLATE ".q($c):"").($_a!=""?" AUTO_INCREMENT=$_a":"");if($E){$Cg=array();if($E["partition_by"]=='RANGE'||$E["partition_by"]=='LIST'){foreach($E["partition_names"]as$x=>$X){$Y=$E["partition_values"][$x];$Cg[]="\n  PARTITION ".idf_escape($X)." VALUES ".($E["partition_by"]=='RANGE'?"LESS THAN":"IN").($Y!=""?" ($Y)":" MAXVALUE");}}$P
.="\nPARTITION BY $E[partition_by]($E[partition])";if($Cg)$P
.=" (".implode(",",$Cg)."\n)";elseif($E["partitions"])$P
.=" PARTITIONS ".(+$E["partitions"]);}elseif($E===null)$P
.="\nREMOVE PARTITIONING";if($R=="")return
queries("CREATE TABLE ".table($B)." (\n".implode(",\n",$b)."\n)$P");if($R!=$B)$b[]="RENAME TO ".table($B);if($P)$b[]=ltrim($P);return($b?queries("ALTER TABLE ".table($R)."\n".implode(",\n",$b)):true);}function
alter_indexes($R,$b){$Ua=array();foreach($b
as$X)$Ua[]=($X[2]=="DROP"?"\nDROP INDEX ".idf_escape($X[1]):"\nADD $X[0] ".($X[0]=="PRIMARY"?"KEY ":"").($X[1]!=""?idf_escape($X[1])." ":"")."(".implode(", ",$X[2]).")");return
queries("ALTER TABLE ".table($R).implode(",",$Ua));}function
truncate_tables(array$T){return
apply_queries("TRUNCATE TABLE",$T);}function
drop_views(array$Nj){return
queries("DROP VIEW ".implode(", ",array_map('Adminer\table',$Nj)));}function
drop_tables(array$T){return
queries("DROP TABLE ".implode(", ",array_map('Adminer\table',$T)));}function
move_tables(array$T,array$Nj,$Ii){$sh=array();foreach($T
as$R)$sh[]=table($R)." TO ".idf_escape($Ii).".".table($R);if(!$sh||queries("RENAME TABLE ".implode(", ",$sh))){$Wb=array();foreach($Nj
as$R)$Wb[table($R)]=view($R);connection()->select_db($Ii);$j=idf_escape(DB);foreach($Wb
as$B=>$Mj){if(!queries("CREATE VIEW $B AS ".str_replace(" $j."," ",$Mj["select"]))||!queries("DROP VIEW $j.$B"))return
false;}return
true;}return
false;}function
copy_tables(array$T,array$Nj,$Ii){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($T
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $B"))||!queries("CREATE TABLE $B LIKE ".table($R))||!queries("INSERT INTO $B SELECT * FROM ".table($R)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K){$hj=$K["Trigger"];if(!queries("CREATE TRIGGER ".($Ii==DB?idf_escape("copy_$hj"):idf_escape($Ii).".".idf_escape($hj))." $K[Timing] $K[Event] ON $B FOR EACH ROW\n$K[Statement];"))return
false;}}foreach($Nj
as$R){$B=($Ii==DB?table("copy_$R"):idf_escape($Ii).".".table($R));$Mj=view($R);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $B"))||!queries("CREATE VIEW $B AS $Mj[select]"))return
false;}return
true;}function
trigger($B,$R){if($B=="")return
array();$L=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($B));return
reset($L);}function
triggers($R){$J=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")))as$K)$J[$K["Trigger"]]=array($K["Timing"],$K["Event"]);return$J;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($B,$U){$ra=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$zc=driver()->enumLength;$mj="((".implode("|",array_merge(array_keys(driver()->types()),$ra)).")\\b(?:\\s*\\(((?:[^'\")]|$zc)++)\\))?"."\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?(?:\\s*COLLATE\\s*['\"]?[^'\"\\s,]+['\"]?)?";$Gg="$gi*(".($U=="FUNCTION"?"":driver()->inout).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$mj";$h=get_val("SHOW CREATE $U ".idf_escape($B),2);preg_match("~\\(((?:$Gg\\s*,?)*)\\)\\s*".($U=="FUNCTION"?"RETURNS\\s+$mj\\s+":"")."(.*)~is",$h,$A);$n=array();preg_match_all("~$Gg\\s*,?~is",$A[1],$Ze,PREG_SET_ORDER);foreach($Ze
as$ug)$n[]=array("field"=>str_replace("``","`",$ug[2]).$ug[3],"type"=>strtolower($ug[5]),"length"=>preg_replace_callback("~$zc~s",'Adminer\normalize_enum',$ug[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$ug[8] $ug[7]"))),"null"=>true,"full_type"=>$ug[4],"inout"=>strtoupper($ug[1]),"collation"=>strtolower($ug[9]),);return
array("fields"=>$n,"comment"=>get_val("SELECT ROUTINE_COMMENT FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_NAME = ".q($B)),)+($U!="FUNCTION"?array("definition"=>$A[11]):array("returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",));}function
routines(){return
get_rows("SELECT SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()");}function
routine_languages(){return
array();}function
routine_id($B,array$K){return
idf_escape($B);}function
last_id($I){return
get_val("SELECT LAST_INSERT_ID()");}function
explain(Db$f,$H){return$f->query("EXPLAIN ".(min_version(5.1)&&!min_version(5.7)?"PARTITIONS ":"").$H);}function
found_rows(array$S,array$Z){return($Z||$S["Engine"]!="InnoDB"?null:$S["Rows"]);}function
create_sql($R,$_a,$si){$J=get_val("SHOW CREATE TABLE ".table($R),1);if(!$_a)$J=preg_replace('~ AUTO_INCREMENT=\d+~','',$J);return$J;}function
truncate_sql($R){return"TRUNCATE ".table($R);}function
use_sql($Nb,$si=""){$B=idf_escape($Nb);$J="";if(preg_match('~CREATE~',$si)&&($h=get_val("SHOW CREATE DATABASE $B",1))){set_utf8mb4($h);if($si=="DROP+CREATE")$J="DROP DATABASE IF EXISTS $B;\n";$J
.="$h;\n";}return$J."USE $B";}function
trigger_sql($R){$J="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($R,"%_\\")),null,"-- ")as$K)$J
.="\nCREATE TRIGGER ".idf_escape($K["Trigger"])." $K[Timing] $K[Event] ON ".table($K["Table"])." FOR EACH ROW\n$K[Statement];;\n";return$J;}function
show_variables(){return
get_rows("SHOW VARIABLES");}function
show_status(){return
get_rows("SHOW STATUS");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
convert_field(array$m){if(preg_match("~binary~",$m["type"]))return"HEX(".idf_escape($m["field"]).")";if($m["type"]=="bit")return"BIN(".idf_escape($m["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($m["field"]).")";}function
unconvert_field(array$m,$J){if(preg_match("~binary~",$m["type"]))$J="UNHEX($J)";if($m["type"]=="bit")$J="CONVERT(b$J, UNSIGNED)";if(preg_match("~geometry|point|linestring|polygon~",$m["type"])){$Sg=(min_version(8)?"ST_":"");$J=$Sg."GeomFromText($J, $Sg"."SRID($m[field]))";}return$J;}function
support($Uc){return
preg_match('~^(comment|columns|copy|database|drop_col|dump|indexes|kill|privileges|move_col|procedure|processlist|routine|sql|status|table|trigger|variables|view'.(min_version(5.1)?'|event':'').(min_version(8)?'|descidx':'').(min_version('8.0.16','10.2.1')?'|check':'').')$~',$Uc);}function
kill_process($t){return
queries("KILL ".number($t));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){return
get_val("SELECT @@max_connections");}function
types(){return
array();}function
type_values($t){return"";}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Fh,$g=null){return
true;}}define('Adminer\JUSH',Driver::$jush);define('Adminer\SERVER',"".$_GET[DRIVER]);define('Adminer\DB',"$_GET[db]");define('Adminer\ME',preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').($_GET["ext"]?"ext=".urlencode($_GET["ext"]).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));function
page_header($Si,$l="",$Ma=array(),$Ti=""){page_headers();if(is_ajax()&&$l){page_messages($l);exit;}if(!ob_get_level())ob_start('ob_gzhandler',4096);$Ui=$Si.($Ti!=""?": $Ti":"");$Vi=strip_tags($Ui.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".adminer()->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>',$Vi,'</title>
<link rel="stylesheet" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=5.4.1"),'">
';$Hb=adminer()->css();if(is_int(key($Hb)))$Hb=array_fill_keys($Hb,'light');$Ed=in_array('light',$Hb)||in_array('',$Hb);$Cd=in_array('dark',$Hb)||in_array('',$Hb);$Kb=($Ed?($Cd?null:false):($Cd?:null));$jf=" media='(prefers-color-scheme: dark)'";if($Kb!==false)echo"<link rel='stylesheet'".($Kb?"":$jf)." href='".h(preg_replace("~\\?.*~","",ME)."?file=dark.css&version=5.4.1")."'>\n";echo"<meta name='color-scheme' content='".($Kb===null?"light dark":($Kb?"dark":"light"))."'>\n",script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=5.4.1");if(adminer()->head($Kb))echo"<link rel='icon' href='data:image/gif;base64,R0lGODlhEAAQAJEAAAQCBPz+/PwCBAROZCH5BAEAAAAALAAAAAAQABAAAAI2hI+pGO1rmghihiUdvUBnZ3XBQA7f05mOak1RWXrNq5nQWHMKvuoJ37BhVEEfYxQzHjWQ5qIAADs='>\n","<link rel='apple-touch-icon' href='".h(preg_replace("~\\?.*~","",ME)."?file=logo.png&version=5.4.1")."'>\n";foreach($Hb
as$_j=>$tf){$ya=($tf=='dark'&&!$Kb?$jf:($tf=='light'&&$Cd?" media='(prefers-color-scheme: light)'":""));echo"<link rel='stylesheet'$ya href='".h($_j)."'>\n";}echo"\n<body class='".'ltr'." nojs";adminer()->bodyClass();echo"'>\n";$o=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($o)&&filemtime($o)+86400>time()){$Lj=unserialize(file_get_contents($o));$ch="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($Lj["version"],base64_decode($Lj["signature"]),$ch)==1)$_COOKIE["adminer_version"]=$Lj["version"];}echo
script("mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick".(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '".VERSION."', '".js_escape(ME)."', '".get_token()."')")."});
document.body.classList.replace('nojs', 'js');
const offlineMessage = '".js_escape('You are offline.')."';
const thousandsSeparator = '".js_escape(',')."';"),"<div id='help' class='jush-".JUSH." jsonly hidden'></div>\n",script("mixin(qs('#help'), {onmouseover: () => { helpOpen = 1; }, onmouseout: helpMouseout});"),"<div id='content'>\n","<span id='menuopen' class='jsonly'>".icon("move","","menu","")."</span>".script("qs('#menuopen').onclick = event => { qs('#foot').classList.toggle('foot'); event.stopPropagation(); }");if($Ma!==null){$_=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($_?:".").'">'.get_driver(DRIVER).'</a> ¬ª ';$_=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$N=adminer()->serverName(SERVER);$N=($N!=""?$N:'Server');if($Ma===false)echo"$N\n";else{echo"<a href='".h($_)."' accesskey='1' title='Alt+Shift+1'>$N</a> ¬ª ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ma)))echo'<a href="'.h($_."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> ¬ª ';if(is_array($Ma)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> ¬ª ';foreach($Ma
as$x=>$X){$Yb=(is_array($X)?$X[1]:h($X));if($Yb!="")echo"<a href='".h(ME."$x=").urlencode(is_array($X)?$X[0]:$X)."'>$Yb</a> ¬ª ";}}echo"$Si\n";}}echo"<h2>$Ui</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($l);$i=&get_session("dbs");if(DB!=""&&$i&&!in_array(DB,$i,true))$i=null;stop_session();define('Adminer\PAGE_HEADER',1);}function
page_headers(){header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach(adminer()->csp(csp())as$Gb){$Gd=array();foreach($Gb
as$x=>$X)$Gd[]="$x $X";header("Content-Security-Policy: ".implode("; ",$Gd));}adminer()->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Ff;if(!$Ff)$Ff=base64_encode(rand_string());return$Ff;}function
page_messages($l){$zj=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$pf=idx($_SESSION["messages"],$zj);if($pf){echo"<div class='message'>".implode("</div>\n<div class='message'>",$pf)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$zj]);}if($l)echo"<div class='error'>$l</div>\n";if(adminer()->error)echo"<div class='error'>".adminer()->error."</div>\n";}function
page_footer($sf=""){echo"</div>\n\n<div id='foot' class='foot'>\n<div id='menu'>\n";adminer()->navigation($sf);echo"</div>\n";if($sf!="auth")echo'<form action="" method="post">
<p class="logout">
<span>',h($_GET["username"])."\n",'</span>
<input type="submit" name="logout" value="Logout" id="logout">
',input_token(),'</form>
';echo"</div>\n\n",script("setupSubmitHighlight(document);");}function
int32($yf){while($yf>=2147483648)$yf-=4294967296;while($yf<=-2147483649)$yf+=4294967296;return(int)$yf;}function
long2str(array$W,$Pj){$Dh='';foreach($W
as$X)$Dh
.=pack('V',$X);if($Pj)return
substr($Dh,0,end($W));return$Dh;}function
str2long($Dh,$Pj){$W=array_values(unpack('V*',str_pad($Dh,4*ceil(strlen($Dh)/4),"\0")));if($Pj)$W[]=strlen($Dh);return$W;}function
xxtea_mx($Wj,$Vj,$vi,$Ae){return
int32((($Wj>>5&0x7FFFFFF)^$Vj<<2)+(($Vj>>3&0x1FFFFFFF)^$Wj<<4))^int32(($vi^$Vj)+($Ae^$Wj));}function
encrypt_string($qi,$x){if($qi=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,true);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=0;while($dh-->0){$vi=int32($vi+0x9E3779B9);$oc=$vi>>2&3;for($sg=0;$sg<$yf;$sg++){$Vj=$W[$sg+1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$sg]+$xf);$W[$sg]=$Wj;}$Vj=$W[0];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Wj=int32($W[$yf]+$xf);$W[$yf]=$Wj;}return
long2str($W,false);}function
decrypt_string($qi,$x){if($qi=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$W=str2long($qi,false);$yf=count($W)-1;$Wj=$W[$yf];$Vj=$W[0];$dh=floor(6+52/($yf+1));$vi=int32($dh*0x9E3779B9);while($vi){$oc=$vi>>2&3;for($sg=$yf;$sg>0;$sg--){$Wj=$W[$sg-1];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[$sg]-$xf);$W[$sg]=$Vj;}$Wj=$W[$yf];$xf=xxtea_mx($Wj,$Vj,$vi,$x[$sg&3^$oc]);$Vj=int32($W[0]-$xf);$W[0]=$Vj;$vi=int32($vi-0x9E3779B9);}return
long2str($W,true);}$Ig=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$X){list($x)=explode(":",$X);$Ig[$x]=$X;}}function
add_invalid_login(){$Fa=get_temp_dir()."/adminer.invalid";foreach(glob("$Fa*")?:array($Fa)as$o){$q=file_open_lock($o);if($q)break;}if(!$q)$q=file_open_lock("$Fa-".rand_string());if(!$q)return;$se=unserialize(stream_get_contents($q));$Pi=time();if($se){foreach($se
as$te=>$X){if($X[0]<$Pi)unset($se[$te]);}}$re=&$se[adminer()->bruteForceKey()];if(!$re)$re=array($Pi+30*60,0);$re[1]++;file_write_unlock($q,serialize($se));}function
check_invalid_login(array&$Ig){$se=array();foreach(glob(get_temp_dir()."/adminer.invalid*")as$o){$q=file_open_lock($o);if($q){$se=unserialize(stream_get_contents($q));file_unlock($q);break;}}$re=idx($se,adminer()->bruteForceKey(),array());$Ef=($re[1]>29?$re[0]-time():0);if($Ef>0)auth_error(lang_format(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Ef/60)),$Ig);}$za=$_POST["auth"];if($za){session_regenerate_id();$Kj=$za["driver"];$N=$za["server"];$V=$za["username"];$F=(string)$za["password"];$j=$za["db"];set_password($Kj,$N,$V,$F);$_SESSION["db"][$Kj][$N][$V][$j]=true;if($za["permanent"]){$x=implode("-",array_map('base64_encode',array($Kj,$N,$V,$j)));$Xg=adminer()->permanentLogin(true);$Ig[$x]="$x:".base64_encode($Xg?encrypt_string($F,$Xg):"");cookie("adminer_permanent",implode(" ",$Ig));}if(count($_POST)==1||DRIVER!=$Kj||SERVER!=$N||$_GET["username"]!==$V||DB!=$j)redirect(auth_url($Kj,$N,$V,$j));}elseif($_POST["logout"]&&(!$_SESSION["token"]||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent($Ig);redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($Ig&&!$_SESSION["pwds"]){session_regenerate_id();$Xg=adminer()->permanentLogin();foreach($Ig
as$x=>$X){list(,$cb)=explode(":",$X);list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));set_password($Kj,$N,$V,decrypt_string(base64_decode($cb),$Xg));$_SESSION["db"][$Kj][$N][$V][$j]=true;}}function
unset_permanent(array&$Ig){foreach($Ig
as$x=>$X){list($Kj,$N,$V,$j)=array_map('base64_decode',explode("-",$x));if($Kj==DRIVER&&$N==SERVER&&$V==$_GET["username"]&&$j==DB)unset($Ig[$x]);}cookie("adminer_permanent",implode(" ",$Ig));}function
auth_error($l,array&$Ig){$Xh=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$Xh]||$_GET[$Xh])&&!$_SESSION["token"])$l='Session expired, please login again.';else{restart_session();add_invalid_login();$F=get_password();if($F!==null){if($F===false)$l
.=($l?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent($Ig);}}if(!$_COOKIE[$Xh]&&$_GET[$Xh]&&ini_bool("session.use_only_cookies"))$l='Session support must be enabled.';$vg=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?:rand_string()),$vg["lifetime"]);if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);page_header('Login',$l,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";adminer()->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists('Adminer\Db')){unset($_SESSION["pwds"][DRIVER]);unset_permanent($Ig);page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",Driver::$extensions)),false);page_footer("auth");exit;}$f='';if(isset($_GET["username"])&&is_string(get_password())){list(,$Mg)=host_port(SERVER);if(preg_match('~^\s*([-+]?\d+)~',$Mg,$A)&&($A[1]<1024||$A[1]>65535))auth_error('Connecting to privileged ports is not allowed.',$Ig);check_invalid_login($Ig);$Fb=adminer()->credentials();$f=Driver::connect($Fb[0],$Fb[1],$Fb[2]);if(is_object($f)){Db::$instance=$f;Driver::$instance=new
Driver($f);if($f->flavor)save_settings(array("vendor-".DRIVER."-".SERVER=>get_driver(DRIVER)));}}$Te=null;if(!is_object($f)||($Te=adminer()->login($_GET["username"],get_password()))!==true){$l=(is_string($f)?nl_br(h($f)):(is_string($Te)?$Te:'Invalid credentials.')).(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':'');auth_error($l,$Ig);}if($_POST["logout"]&&$_SESSION["token"]&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if(!$_SESSION["token"])$_SESSION["token"]=rand(1,1e6);stop_session(true);if($za&&$_POST["token"])$_POST["token"]=get_token();$l='';if($_POST){if(!verify_token()){$ke="max_input_vars";$hf=ini_get($ke);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$x){$X=ini_get($x);if($X&&(!$hf||$X<$hf)){$ke=$x;$hf=$X;}}}$l=(!$_POST["token"]&&$hf?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$ke'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$l=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$l
.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
print_select_result($I,$g=null,array$hg=array(),$z=0){$Re=array();$w=array();$e=array();$Ka=array();$nj=array();$J=array();for($s=0;(!$z||$s<$z)&&($K=$I->fetch_row());$s++){if(!$s){echo"<div class='scrollable'>\n","<table class='nowrap odds'>\n","<thead><tr>";for($ze=0;$ze<count($K);$ze++){$m=$I->fetch_field();$B=$m->name;$gg=(isset($m->orgtable)?$m->orgtable:"");$fg=(isset($m->orgname)?$m->orgname:$B);if($hg&&JUSH=="sql")$Re[$ze]=($B=="table"?"table=":($B=="possible_keys"?"indexes=":null));elseif($gg!=""){if(isset($m->table))$J[$m->table]=$gg;if(!isset($w[$gg])){$w[$gg]=array();foreach(indexes($gg,$g)as$v){if($v["type"]=="PRIMARY"){$w[$gg]=array_flip($v["columns"]);break;}}$e[$gg]=$w[$gg];}if(isset($e[$gg][$fg])){unset($e[$gg][$fg]);$w[$gg][$fg]=$ze;$Re[$ze]=$gg;}}if($m->charsetnr==63)$Ka[$ze]=true;$nj[$ze]=$m->type;echo"<th".($gg!=""||$m->name!=$fg?" title='".h(($gg!=""?"$gg.":"").$fg)."'":"").">".h($B).($hg?doc_link(array('sql'=>"explain-output.html#explain_".strtolower($B),'mariadb'=>"explain/#the-columns-in-explain-select",)):"");}echo"</thead>\n";}echo"<tr>";foreach($K
as$x=>$X){$_="";if(isset($Re[$x])&&!$e[$Re[$x]]){if($hg&&JUSH=="sql"){$R=$K[array_search("table=",$Re)];$_=ME.$Re[$x].urlencode($hg[$R]!=""?$hg[$R]:$R);}else{$_=ME."edit=".urlencode($Re[$x]);foreach($w[$Re[$x]]as$hb=>$ze){if($K[$ze]===null){$_="";break;}$_
.="&where".urlencode("[".bracket_escape($hb)."]")."=".urlencode($K[$ze]);}}}elseif(is_url($X))$_=$X;if($X===null)$X="<i>NULL</i>";elseif($Ka[$x]&&!is_utf8($X))$X="<i>".lang_format(array('%d byte','%d bytes'),strlen($X))."</i>";else{$X=h($X);if($nj[$x]==254)$X="<code>$X</code>";}if($_)$X="<a href='".h($_)."'".(is_url($_)?target_blank():'').">$X</a>";echo"<td".($nj[$x]<=9||$nj[$x]==246?" class='number'":"").">$X";}}echo($s?"</table>\n</div>":"<p class='message'>".'No rows.')."\n";return$J;}function
referencable_primary($Ph){$J=array();foreach(table_status('',true)as$_i=>$R){if($_i!=$Ph&&fk_support($R)){foreach(fields($_i)as$m){if($m["primary"]){if($J[$_i]){unset($J[$_i]);break;}$J[$_i]=$m;}}}}return$J;}function
textarea($B,$Y,$L=10,$kb=80){echo"<textarea name='".h($B)."' rows='$L' cols='$kb' class='sqlarea jush-".JUSH."' spellcheck='false' wrap='off'>";if(is_array($Y)){foreach($Y
as$X)echo
h($X[0])."\n\n\n";}else
echo
h($Y);echo"</textarea>";}function
select_input($ya,array$bg,$Y="",$Vf="",$Jg=""){$Hi=($bg?"select":"input");return"<$Hi$ya".($bg?"><option value=''>$Jg".optionlist($bg,$Y,true)."</select>":" size='10' value='".h($Y)."' placeholder='$Jg'>").($Vf?script("qsl('$Hi').onchange = $Vf;",""):"");}function
json_row($x,$X=null,$Fc=true){static$bd=true;if($bd)echo"{";if($x!=""){echo($bd?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($X!==null?($Fc?'"'.addcslashes($X,"\r\n\"\\/").'"':$X):'null');$bd=false;}else{echo"\n}\n";$bd=true;}}function
edit_type($x,array$m,array$jb,array$ld=array(),array$Rc=array()){$U=$m["type"];echo"<td><select name='".h($x)."[type]' class='type' aria-labelledby='label-type'>";if($U&&!array_key_exists($U,driver()->types())&&!isset($ld[$U])&&!in_array($U,$Rc))$Rc[]=$U;$ri=driver()->structuredTypes();if($ld)$ri['Foreign keys']=$ld;echo
optionlist(array_merge($Rc,$ri),$U),"</select><td>","<input name='".h($x)."[length]' value='".h($m["length"])."' size='3'".(!$m["length"]&&preg_match('~var(char|binary)$~',$U)?" class='required'":"")." aria-labelledby='label-length'>","<td class='options'>",($jb?"<input list='collations' name='".h($x)."[collation]'".(preg_match('~(char|text|enum|set)$~',$U)?"":" class='hidden'")." value='".h($m["collation"])."' placeholder='(".'collation'.")'>":''),(driver()->unsigned?"<select name='".h($x)."[unsigned]'".(!$U||preg_match(number_type(),$U)?"":" class='hidden'").'><option>'.optionlist(driver()->unsigned,$m["unsigned"]).'</select>':''),(isset($m['on_update'])?"<select name='".h($x)."[on_update]'".(preg_match('~timestamp|datetime~',$U)?"":" class='hidden'").'>'.optionlist(array(""=>"(".'ON UPDATE'.")","CURRENT_TIMESTAMP"),(preg_match('~^CURRENT_TIMESTAMP~i',$m["on_update"])?"CURRENT_TIMESTAMP":$m["on_update"])).'</select>':''),($ld?"<select name='".h($x)."[on_delete]'".(preg_match("~`~",$U)?"":" class='hidden'")."><option value=''>(".'ON DELETE'.")".optionlist(explode("|",driver()->onActions),$m["on_delete"])."</select> ":" ");}function
process_length($y){$Ac=driver()->enumLength;return(preg_match("~^\\s*\\(?\\s*$Ac(?:\\s*,\\s*$Ac)*+\\s*\\)?\\s*\$~",$y)&&preg_match_all("~$Ac~",$y,$Ze)?"(".implode(",",$Ze[0]).")":preg_replace('~^[0-9].*~','(\0)',preg_replace('~[^-0-9,+()[\]]~','',$y)));}function
process_type(array$m,$ib="COLLATE"){return" $m[type]".process_length($m["length"]).(preg_match(number_type(),$m["type"])&&in_array($m["unsigned"],driver()->unsigned)?" $m[unsigned]":"").(preg_match('~char|text|enum|set~',$m["type"])&&$m["collation"]?" $ib ".(JUSH=="mssql"?$m["collation"]:q($m["collation"])):"");}function
process_field(array$m,array$lj){if($m["on_update"])$m["on_update"]=str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",$m["on_update"]);return
array(idf_escape(trim($m["field"])),process_type($lj),($m["null"]?" NULL":" NOT NULL"),default_value($m),(preg_match('~timestamp|datetime~',$m["type"])&&$m["on_update"]?" ON UPDATE $m[on_update]":""),(support("comment")&&$m["comment"]!=""?" COMMENT ".q($m["comment"]):""),($m["auto_increment"]?auto_increment():null),);}function
default_value(array$m){$k=$m["default"];$sd=$m["generated"];return($k===null?"":(in_array($sd,driver()->generated)?(JUSH=="mssql"?" AS ($k)".($sd=="VIRTUAL"?"":" $sd")."":" GENERATED ALWAYS AS ($k) $sd"):" DEFAULT ".(!preg_match('~^GENERATED ~i',$k)&&(preg_match('~char|binary|text|json|enum|set~',$m["type"])||preg_match('~^(?![a-z])~i',$k))?(JUSH=="sql"&&preg_match('~text|json~',$m["type"])?"(".q($k).")":q($k)):str_ireplace("current_timestamp()","CURRENT_TIMESTAMP",(JUSH=="sqlite"?"($k)":$k)))));}function
type_class($U){foreach(array('char'=>'text','date'=>'time|year','binary'=>'blob','enum'=>'set',)as$x=>$X){if(preg_match("~$x|$X~",$U))return" class='$x'";}}function
edit_fields(array$n,array$jb,$U="TABLE",array$ld=array()){$n=array_values($n);$Tb=(($_POST?$_POST["defaults"]:get_setting("defaults"))?"":" class='hidden'");$pb=(($_POST?$_POST["comments"]:get_setting("comments"))?"":" class='hidden'");echo"<thead><tr>\n",($U=="PROCEDURE"?"<td>":""),"<th id='label-name'>".($U=="TABLE"?'Column name':'Parameter name'),"<td id='label-type'>".'Type'."<textarea id='enum-edit' rows='4' cols='12' wrap='off' style='display: none;'></textarea>".script("qs('#enum-edit').onblur = editingLengthBlur;"),"<td id='label-length'>".'Length',"<td>".'Options';if($U=="TABLE")echo"<td id='label-null'>NULL\n","<td><input type='radio' name='auto_increment_col' value=''><abbr id='label-ai' title='".'Auto Increment'."'>AI</abbr>",doc_link(array('sql'=>"example-auto-increment.html",'mariadb'=>"auto_increment/",'sqlite'=>"autoinc.html",'pgsql'=>"datatype-numeric.html#DATATYPE-SERIAL",'mssql'=>"t-sql/statements/create-table-transact-sql-identity-property",)),"<td id='label-default'$Tb>".'Default value',(support("comment")?"<td id='label-comment'$pb>".'Comment':"");echo"<td>".icon("plus","add[".(support("move_col")?0:count($n))."]","+",'Add next'),"</thead>\n<tbody>\n",script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");foreach($n
as$s=>$m){$s++;$ig=$m[($_POST?"orig":"field")];$ec=(isset($_POST["add"][$s-1])||(isset($m["field"])&&!idx($_POST["drop_col"],$s)))&&(support("drop_col")||$ig=="");echo"<tr".($ec?"":" style='display: none;'").">\n",($U=="PROCEDURE"?"<td>".html_select("fields[$s][inout]",explode("|",driver()->inout),$m["inout"]):"")."<th>";if($ec)echo"<input name='fields[$s][field]' value='".h($m["field"])."' data-maxlength='64' autocapitalize='off' aria-labelledby='label-name'".(isset($_POST["add"][$s-1])?" autofocus":"").">";echo
input_hidden("fields[$s][orig]",$ig);edit_type("fields[$s]",$m,$jb,$ld);if($U=="TABLE")echo"<td>".checkbox("fields[$s][null]",1,$m["null"],"","","block","label-null"),"<td><label class='block'><input type='radio' name='auto_increment_col' value='$s'".($m["auto_increment"]?" checked":"")." aria-labelledby='label-ai'></label>","<td$Tb>".(driver()->generated?html_select("fields[$s][generated]",array_merge(array("","DEFAULT"),driver()->generated),$m["generated"])." ":checkbox("fields[$s][generated]",1,$m["generated"],"","","","label-default")),"<input name='fields[$s][default]' value='".h($m["default"])."' aria-labelledby='label-default'>",(support("comment")?"<td$pb><input name='fields[$s][comment]' value='".h($m["comment"])."' data-maxlength='".(min_version(5.5)?1024:255)."' aria-labelledby='label-comment'>":"");echo"<td>",(support("move_col")?icon("plus","add[$s]","+",'Add next')." ".icon("up","up[$s]","‚Üë",'Move up')." ".icon("down","down[$s]","‚Üì",'Move down')." ":""),($ig==""||support("drop_col")?icon("cross","drop_col[$s]","x",'Remove'):"");}}function
process_fields(array&$n){$C=0;if($_POST["up"]){$Ie=0;foreach($n
as$x=>$m){if(key($_POST["up"])==$x){unset($n[$x]);array_splice($n,$Ie,0,array($m));break;}if(isset($m["field"]))$Ie=$C;$C++;}}elseif($_POST["down"]){$nd=false;foreach($n
as$x=>$m){if(isset($m["field"])&&$nd){unset($n[key($_POST["down"])]);array_splice($n,$C,0,array($nd));break;}if(key($_POST["down"])==$x)$nd=$m;$C++;}}elseif($_POST["add"]){$n=array_values($n);array_splice($n,key($_POST["add"]),0,array(array()));}elseif(!$_POST["drop_col"])return
false;return
true;}function
normalize_enum(array$A){$X=$A[0];return"'".str_replace("'","''",addcslashes(stripcslashes(str_replace($X[0].$X[0],$X[0],substr($X,1,-1))),'\\'))."'";}function
grant($ud,array$Zg,$e,$Sf){if(!$Zg)return
true;if($Zg==array("ALL PRIVILEGES","GRANT OPTION"))return($ud=="GRANT"?queries("$ud ALL PRIVILEGES$Sf WITH GRANT OPTION"):queries("$ud ALL PRIVILEGES$Sf")&&queries("$ud GRANT OPTION$Sf"));return
queries("$ud ".preg_replace('~(GRANT OPTION)\([^)]*\)~','\1',implode("$e, ",$Zg).$e).$Sf);}function
drop_create($ic,$h,$kc,$Li,$mc,$Se,$of,$mf,$nf,$Pf,$Bf){if($_POST["drop"])query_redirect($ic,$Se,$of);elseif($Pf=="")query_redirect($h,$Se,$nf);elseif($Pf!=$Bf){$Eb=queries($h);queries_redirect($Se,$mf,$Eb&&queries($ic));if($Eb)queries($kc);}else
queries_redirect($Se,$mf,queries($Li)&&queries($mc)&&queries($ic)&&queries($h));}function
create_trigger($Sf,array$K){$Ri=" $K[Timing] $K[Event]".(preg_match('~ OF~',$K["Event"])?" $K[Of]":"");return"CREATE TRIGGER ".idf_escape($K["Trigger"]).(JUSH=="mssql"?$Sf.$Ri:$Ri.$Sf).rtrim(" $K[Type]\n$K[Statement]",";").";";}function
create_routine($_h,array$K){$O=array();$n=(array)$K["fields"];ksort($n);foreach($n
as$m){if($m["field"]!="")$O[]=(preg_match("~^(".driver()->inout.")\$~",$m["inout"])?"$m[inout] ":"").idf_escape($m["field"]).process_type($m,"CHARACTER SET");}$Vb=rtrim($K["definition"],";");return"CREATE $_h ".idf_escape(trim($K["name"]))." (".implode(", ",$O).")".($_h=="FUNCTION"?" RETURNS".process_type($K["returns"],"CHARACTER SET"):"").($K["language"]?" LANGUAGE $K[language]":"").(JUSH=="pgsql"?" AS ".q($Vb):"\n$Vb;");}function
remove_definer($H){return
preg_replace('~^([A-Z =]+) DEFINER=`'.preg_replace('~@(.*)~','`@`(%|\1)',logged_user()).'`~','\1',$H);}function
format_foreign_key(array$p){$j=$p["db"];$Gf=$p["ns"];return" FOREIGN KEY (".implode(", ",array_map('Adminer\idf_escape',$p["source"])).") REFERENCES ".($j!=""&&$j!=$_GET["db"]?idf_escape($j).".":"").($Gf!=""&&$Gf!=$_GET["ns"]?idf_escape($Gf).".":"").idf_escape($p["table"])." (".implode(", ",array_map('Adminer\idf_escape',$p["target"])).")".(preg_match("~^(".driver()->onActions.")\$~",$p["on_delete"])?" ON DELETE $p[on_delete]":"").(preg_match("~^(".driver()->onActions.")\$~",$p["on_update"])?" ON UPDATE $p[on_update]":"");}function
tar_file($o,$Wi){$J=pack("a100a8a8a8a12a12",$o,644,0,0,decoct($Wi->size),decoct(time()));$bb=8*32;for($s=0;$s<strlen($J);$s++)$bb+=ord($J[$s]);$J
.=sprintf("%06o",$bb)."\0 ";echo$J,str_repeat("\0",512-strlen($J));$Wi->send();echo
str_repeat("\0",511-($Wi->size+511)%512);}function
doc_link(array$Fg,$Mi="<sup>?</sup>"){$Vh=connection()->server_info;$Lj=preg_replace('~^(\d\.?\d).*~s','\1',$Vh);$Aj=array('sql'=>"https://dev.mysql.com/doc/refman/$Lj/en/",'sqlite'=>"https://www.sqlite.org/",'pgsql'=>"https://www.postgresql.org/docs/".(connection()->flavor=='cockroach'?"current":$Lj)."/",'mssql'=>"https://learn.microsoft.com/en-us/sql/",'oracle'=>"https://www.oracle.com/pls/topic/lookup?ctx=db".preg_replace('~^.* (\d+)\.(\d+)\.\d+\.\d+\.\d+.*~s','\1\2',$Vh)."&id=",);if(connection()->flavor=='maria'){$Aj['sql']="https://mariadb.com/kb/en/";$Fg['sql']=(isset($Fg['mariadb'])?$Fg['mariadb']:str_replace(".html","/",$Fg['sql']));}return($Fg[JUSH]?"<a href='".h($Aj[JUSH].$Fg[JUSH].(JUSH=='mssql'?"?view=sql-server-ver$Lj":""))."'".target_blank().">$Mi</a>":"");}function
db_size($j){if(!connection()->select_db($j))return"?";$J=0;foreach(table_status()as$S)$J+=$S["Data_length"]+$S["Index_length"];return
format_number($J);}function
set_utf8mb4($h){static$O=false;if(!$O&&preg_match('~\butf8mb4~i',$h)){$O=true;echo"SET NAMES ".charset(connection()).";\n\n";}}if(isset($_GET["status"]))$_GET["variables"]=$_GET["status"];if(isset($_GET["import"]))$_GET["sql"]=$_GET["import"];if(!(DB!=""?connection()->select_db(DB):isset($_GET["sql"])||isset($_GET["dump"])||isset($_GET["database"])||isset($_GET["processlist"])||isset($_GET["privileges"])||isset($_GET["user"])||isset($_GET["variables"])||$_GET["script"]=="connect"||$_GET["script"]=="kill")){if(DB!=""||$_GET["refresh"]){restart_session();set_session("dbs",null);}if(DB!=""){header("HTTP/1.1 404 Not Found");page_header('Database'.": ".h(DB),'Invalid database.',true);}else{if($_POST["db"]&&!$l)queries_redirect(substr(ME,0,-1),'Databases have been dropped.',drop_databases($_POST["db"]));page_header('Select database',$l,false);echo"<p class='links'>\n";foreach(array('database'=>'Create database','privileges'=>'Privileges','processlist'=>'Process list','variables'=>'Variables','status'=>'Status',)as$x=>$X){if(support($x))echo"<a href='".h(ME)."$x='>$X</a>\n";}echo"<p>".sprintf('%s version: %s through PHP extension %s',get_driver(DRIVER),"<b>".h(connection()->server_info)."</b>","<b>".connection()->extension."</b>")."\n","<p>".sprintf('Logged as: %s',"<b>".h(logged_user())."</b>")."\n";$i=adminer()->databases();if($i){$Hh=support("scheme");$jb=collations();echo"<form action='' method='post'>\n","<table class='checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),"<thead><tr>".(support("database")?"<td>":"")."<th>".'Database'.(get_session("dbs")!==null?" - <a href='".h(ME)."refresh=1'>".'Refresh'."</a>":"")."<td>".'Collation'."<td>".'Tables'."<td>".'Size'." - <a href='".h(ME)."dbsize=1'>".'Compute'."</a>".script("qsl('a').onclick = partial(ajaxSetHtml, '".js_escape(ME)."script=connect');","")."</thead>\n";$i=($_GET["dbsize"]?count_tables($i):array_flip($i));foreach($i
as$j=>$T){$zh=h(ME)."db=".urlencode($j);$t=h("Db-".$j);echo"<tr>".(support("database")?"<td>".checkbox("db[]",$j,in_array($j,(array)$_POST["db"]),"","","",$t):""),"<th><a href='$zh' id='$t'>".h($j)."</a>";$c=h(db_collation($j,$jb));echo"<td>".(support("database")?"<a href='$zh".($Hh?"&amp;ns=":"")."&amp;database=' title='".'Alter database'."'>$c</a>":$c),"<td align='right'><a href='$zh&amp;schema=' id='tables-".h($j)."' title='".'Database schema'."'>".($_GET["dbsize"]?$T:"?")."</a>","<td align='right' id='size-".h($j)."'>".($_GET["dbsize"]?db_size($j):"?"),"\n";}echo"</table>\n",(support("database")?"<div class='footer'><div>\n"."<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>\n".input_hidden("all").script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };")."<input type='submit' name='drop' value='".'Drop'."'>".confirm()."\n"."</div></fieldset>\n"."</div></div>\n":""),input_token(),"</form>\n",script("tableCheck();");}if(!empty(adminer()->plugins)){echo"<div class='plugins'>\n","<h3>".'Loaded plugins'."</h3>\n<ul>\n";foreach(adminer()->plugins
as$Kg){$Zb=(method_exists($Kg,'description')?$Kg->description():"");if(!$Zb){$oh=new
\ReflectionObject($Kg);if(preg_match('~^/[\s*]+(.+)~',$oh->getDocComment(),$A))$Zb=$A[1];}$Ih=(method_exists($Kg,'screenshot')?$Kg->screenshot():"");echo"<li><b>".get_class($Kg)."</b>".h($Zb?": $Zb":"").($Ih?" (<a href='".h($Ih)."'".target_blank().">".'screenshot'."</a>)":"")."\n";}echo"</ul>\n";adminer()->pluginsLinks();echo"</div>\n";}}page_footer("db");exit;}if(support("scheme")){if(DB!=""&&$_GET["ns"]!==""){if(!isset($_GET["ns"]))redirect(preg_replace('~ns=[^&]*&~','',ME)."ns=".get_schema());if(!set_schema($_GET["ns"])){header("HTTP/1.1 404 Not Found");page_header('Schema'.": ".h($_GET["ns"]),'Invalid schema.',true);page_footer("ns");exit;}}}adminer()->afterConnect();class
TmpFile{private$handler;var$size;function
__construct(){$this->handler=tmpfile();}function
write($zb){$this->size+=strlen($zb);fwrite($this->handler,$zb);}function
send(){fseek($this->handler,0);fpassthru($this->handler);fclose($this->handler);}}if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["callf"]))$_GET["call"]=$_GET["callf"];if(isset($_GET["function"]))$_GET["procedure"]=$_GET["function"];if(isset($_GET["download"])){$a=$_GET["download"];$n=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$M=array(idf_escape($_GET["field"]));$I=driver()->select($a,$M,array(where($_GET,$n)),$M);$K=($I?$I->fetch_row():array());echo
driver()->value($K[0],$n[$_GET["field"]]);exit;}elseif(isset($_GET["table"])){$a=$_GET["table"];$n=fields($a);if(!$n)$l=error()?:'No tables.';$S=table_status1($a);$B=adminer()->tableName($S);page_header(($n&&is_view($S)?$S['Engine']=='materialized view'?'Materialized view':'View':'Table').": ".($B!=""?$B:h($a)),$l);$yh=array();foreach($n
as$x=>$m)$yh+=$m["privileges"];adminer()->selectLinks($S,(isset($yh["insert"])||!support("table")?"":null));$ob=$S["Comment"];if($ob!="")echo"<p class='nowrap'>".'Comment'.": ".h($ob)."\n";if($n)adminer()->tableStructurePrint($n,$S);function
tables_links(array$T){echo"<ul>\n";foreach($T
as$R)echo"<li><a href='".h(ME."table=".urlencode($R))."'>".h($R)."</a>";echo"</ul>\n";}$je=driver()->inheritsFrom($a);if($je){echo"<h3>".'Inherits from'."</h3>\n";tables_links($je);}if(support("indexes")&&driver()->supportsIndex($S)){echo"<h3 id='indexes'>".'Indexes'."</h3>\n";$w=indexes($a);if($w)adminer()->tableIndexesPrint($w,$S);echo'<p class="links"><a href="'.h(ME).'indexes='.urlencode($a).'">'.'Alter indexes'."</a>\n";}if(!is_view($S)){if(fk_support($S)){echo"<h3 id='foreign-keys'>".'Foreign keys'."</h3>\n";$ld=foreign_keys($a);if($ld){echo"<table>\n","<thead><tr><th>".'Source'."<td>".'Target'."<td>".'ON DELETE'."<td>".'ON UPDATE'."<td></thead>\n";foreach($ld
as$B=>$p){echo"<tr title='".h($B)."'>","<th><i>".implode("</i>, <i>",array_map('Adminer\h',$p["source"]))."</i>";$_=($p["db"]!=""?preg_replace('~db=[^&]*~',"db=".urlencode($p["db"]),ME):($p["ns"]!=""?preg_replace('~ns=[^&]*~',"ns=".urlencode($p["ns"]),ME):ME));echo"<td><a href='".h($_."table=".urlencode($p["table"]))."'>".($p["db"]!=""&&$p["db"]!=DB?"<b>".h($p["db"])."</b>.":"").($p["ns"]!=""&&$p["ns"]!=$_GET["ns"]?"<b>".h($p["ns"])."</b>.":"").h($p["table"])."</a>","(<i>".implode("</i>, <i>",array_map('Adminer\h',$p["target"]))."</i>)","<td>".h($p["on_delete"]),"<td>".h($p["on_update"]),'<td><a href="'.h(ME.'foreign='.urlencode($a).'&name='.urlencode($B)).'">'.'Alter'.'</a>',"\n";}echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'foreign='.urlencode($a).'">'.'Add foreign key'."</a>\n";}if(support("check")){echo"<h3 id='checks'>".'Checks'."</h3>\n";$Xa=driver()->checkConstraints($a);if($Xa){echo"<table>\n";foreach($Xa
as$x=>$X)echo"<tr title='".h($x)."'>","<td><code class='jush-".JUSH."'>".h($X),"<td><a href='".h(ME.'check='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>","\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'check='.urlencode($a).'">'.'Create check'."</a>\n";}}if(support(is_view($S)?"view_trigger":"trigger")){echo"<h3 id='triggers'>".'Triggers'."</h3>\n";$kj=triggers($a);if($kj){echo"<table>\n";foreach($kj
as$x=>$X)echo"<tr valign='top'><td>".h($X[0])."<td>".h($X[1])."<th>".h($x)."<td><a href='".h(ME.'trigger='.urlencode($a).'&name='.urlencode($x))."'>".'Alter'."</a>\n";echo"</table>\n";}echo'<p class="links"><a href="'.h(ME).'trigger='.urlencode($a).'">'.'Add trigger'."</a>\n";}$ie=driver()->inheritedTables($a);if($ie){echo"<h3 id='partitions'>".'Inherited by'."</h3>\n";$zg=driver()->partitionsInfo($a);if($zg)echo"<p><code class='jush-".JUSH."'>BY ".h("$zg[partition_by]($zg[partition])")."</code>\n";tables_links($ie);}}elseif(isset($_GET["schema"])){page_header('Database schema',"",array(),h(DB.($_GET["ns"]?".$_GET[ns]":"")));$Bi=array();$Ci=array();$ca=($_GET["schema"]?:$_COOKIE["adminer_schema-".str_replace(".","_",DB)]);preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~',$ca,$Ze,PREG_SET_ORDER);foreach($Ze
as$s=>$A){$Bi[$A[1]]=array($A[2],$A[3]);$Ci[]="\n\t'".js_escape($A[1])."': [ $A[2], $A[3] ]";}$Zi=0;$Ga=-1;$Fh=array();$nh=array();$Me=array();$sa=driver()->allFields();foreach(table_status('',true)as$R=>$S){if(is_view($S))continue;$Ng=0;$Fh[$R]["fields"]=array();foreach($sa[$R]as$m){$Ng+=1.25;$m["pos"]=$Ng;$Fh[$R]["fields"][$m["field"]]=$m;}$Fh[$R]["pos"]=($Bi[$R]?:array($Zi,0));foreach(adminer()->foreignKeys($R)as$X){if(!$X["db"]){$Ke=$Ga;if(idx($Bi[$R],1)||idx($Bi[$X["table"]],1))$Ke=min(idx($Bi[$R],1,0),idx($Bi[$X["table"]],1,0))-1;else$Ga-=.1;while($Me[(string)$Ke])$Ke-=.0001;$Fh[$R]["references"][$X["table"]][(string)$Ke]=array($X["source"],$X["target"]);$nh[$X["table"]][$R][(string)$Ke]=$X["target"];$Me[(string)$Ke]=true;}}$Zi=max($Zi,$Fh[$R]["pos"][0]+2.5+$Ng);}echo'<div id="schema" style="height: ',$Zi,'em;">
<script',nonce(),'>
qs(\'#schema\').onselectstart = () => false;
const tablePos = {',implode(",",$Ci)."\n",'};
const em = qs(\'#schema\').offsetHeight / ',$Zi,';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'',js_escape(DB),'\');
</script>
';foreach($Fh
as$B=>$R){echo"<div class='table' style='top: ".$R["pos"][0]."em; left: ".$R["pos"][1]."em;'>",'<a href="'.h(ME).'table='.urlencode($B).'"><b>'.h($B)."</b></a>",script("qsl('div').onmousedown = schemaMousedown;");foreach($R["fields"]as$m){$X='<span'.type_class($m["type"]).' title="'.h($m["type"].($m["length"]?"($m[length])":"").($m["null"]?" NULL":'')).'">'.h($m["field"]).'</span>';echo"<br>".($m["primary"]?"<i>$X</i>":$X);}foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($kh[0]as$fi)echo"\n<div class='references' title='".h($Ji)."' id='refs$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$fi]["pos"]."em; padding-top: .5em;'>"."<div style='border-top: 1px solid gray; width: ".(-$Le)."em;'></div></div>";}}foreach((array)$nh[$B]as$Ji=>$ph){foreach($ph
as$Ke=>$e){$Le=$Ke-idx($Bi[$B],1);$s=0;foreach($e
as$Ii)echo"\n<div class='references arrow' title='".h($Ji)."' id='refd$Ke-".($s++)."' style='left: $Le"."em; top: ".$R["fields"][$Ii]["pos"]."em;'>"."<div style='height: .5em; border-bottom: 1px solid gray; width: ".(-$Le)."em;'></div>"."</div>";}}echo"\n</div>\n";}foreach($Fh
as$B=>$R){foreach((array)$R["references"]as$Ji=>$ph){foreach($ph
as$Ke=>$kh){$rf=$Zi;$ff=-10;foreach($kh[0]as$x=>$fi){$Og=$R["pos"][0]+$R["fields"][$fi]["pos"];$Pg=$Fh[$Ji]["pos"][0]+$Fh[$Ji]["fields"][$kh[1][$x]]["pos"];$rf=min($rf,$Og,$Pg);$ff=max($ff,$Og,$Pg);}echo"<div class='references' id='refl$Ke' style='left: $Ke"."em; top: $rf"."em; padding: .5em 0;'><div style='border-right: 1px solid gray; margin-top: 1px; height: ".($ff-$rf)."em;'></div></div>\n";}}}echo'</div>
<p class="links"><a href="',h(ME."schema=".urlencode($ca)),'" id="schema-link">Permanent link</a>
';}elseif(isset($_GET["dump"])){$a=$_GET["dump"];if($_POST&&!$l){save_settings(array_intersect_key($_POST,array_flip(array("output","format","db_style","types","routines","events","table_style","auto_increment","triggers","data_style"))),"adminer_export");$T=array_flip((array)$_POST["tables"])+array_flip((array)$_POST["data"]);$Nc=dump_headers((count($T)==1?key($T):DB),(DB==""||count($T)>1));$we=preg_match('~sql~',$_POST["format"]);if($we){echo"-- Adminer ".VERSION." ".get_driver(DRIVER)." ".str_replace("\n"," ",connection()->server_info)." dump\n\n";if(JUSH=="sql"){echo"SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
".($_POST["data_style"]?"SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
":"")."
";connection()->query("SET time_zone = '+00:00'");connection()->query("SET sql_mode = ''");}}$si=$_POST["db_style"];$i=array(DB);if(DB==""){$i=$_POST["databases"];if(is_string($i))$i=explode("\n",rtrim(str_replace("\r","",$i),"\n"));}foreach((array)$i
as$j){adminer()->dumpDatabase($j);if(connection()->select_db($j)){if($we){if($si)echo
use_sql($j,$si).";\n\n";$pg="";if($_POST["types"]){foreach(types()as$t=>$U){$Bc=type_values($t);if($Bc)$pg
.=($si!='DROP+CREATE'?"DROP TYPE IF EXISTS ".idf_escape($U).";;\n":"")."CREATE TYPE ".idf_escape($U)." AS ENUM ($Bc);\n\n";else$pg
.="-- Could not export type $U\n\n";}}if($_POST["routines"]){foreach(routines()as$K){$B=$K["ROUTINE_NAME"];$_h=$K["ROUTINE_TYPE"];$h=create_routine($_h,array("name"=>$B)+routine($K["SPECIFIC_NAME"],$_h));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP $_h IF EXISTS ".idf_escape($B).";;\n":"")."$h;\n\n";}}if($_POST["events"]){foreach(get_rows("SHOW EVENTS",null,"-- ")as$K){$h=remove_definer(get_val("SHOW CREATE EVENT ".idf_escape($K["Name"]),3));set_utf8mb4($h);$pg
.=($si!='DROP+CREATE'?"DROP EVENT IF EXISTS ".idf_escape($K["Name"]).";;\n":"")."$h;;\n\n";}}echo($pg&&JUSH=='sql'?"DELIMITER ;;\n\n$pg"."DELIMITER ;\n\n":$pg);}if($_POST["table_style"]||$_POST["data_style"]){$Nj=array();foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));$Lb=(DB==""||in_array($B,(array)$_POST["data"]));if($R||$Lb){$Wi=null;if($Nc=="tar"){$Wi=new
TmpFile;ob_start(array($Wi,'write'),1e5);}adminer()->dumpTable($B,($R?$_POST["table_style"]:""),(is_view($S)?2:0));if(is_view($S))$Nj[]=$B;elseif($Lb){$n=fields($B);adminer()->dumpData($B,$_POST["data_style"],"SELECT *".convert_fields($n,$n)." FROM ".table($B));}if($we&&$_POST["triggers"]&&$R&&($kj=trigger_sql($B)))echo"\nDELIMITER ;;\n$kj\nDELIMITER ;\n";if($Nc=="tar"){ob_end_flush();tar_file((DB!=""?"":"$j/")."$B.csv",$Wi);}elseif($we)echo"\n";}}if(function_exists('Adminer\foreign_keys_sql')){foreach(table_status('',true)as$B=>$S){$R=(DB==""||in_array($B,(array)$_POST["tables"]));if($R&&!is_view($S))echo
foreign_keys_sql($B);}}foreach($Nj
as$Mj)adminer()->dumpTable($Mj,$_POST["table_style"],1);if($Nc=="tar")echo
pack("x512");}}}adminer()->dumpFooter();exit;}page_header('Export',$l,($_GET["export"]!=""?array("table"=>$_GET["export"]):array()),h(DB));echo'
<form action="" method="post">
<table class="layout">
';$Pb=array('','USE','DROP+CREATE','CREATE');$Di=array('','DROP+CREATE','CREATE');$Mb=array('','TRUNCATE+INSERT','INSERT');if(JUSH=="sql")$Mb[]='INSERT+UPDATE';$K=get_settings("adminer_export");if(!$K)$K=array("output"=>"text","format"=>"sql","db_style"=>(DB!=""?"":"CREATE"),"table_style"=>"DROP+CREATE","data_style"=>"INSERT");if(!isset($K["events"])){$K["routines"]=$K["events"]=($_GET["dump"]=="");$K["triggers"]=$K["table_style"];}echo"<tr><th>".'Output'."<td>".html_radios("output",adminer()->dumpOutput(),$K["output"])."\n","<tr><th>".'Format'."<td>".html_radios("format",adminer()->dumpFormat(),$K["format"])."\n",(JUSH=="sqlite"?"":"<tr><th>".'Database'."<td>".html_select('db_style',$Pb,$K["db_style"]).(support("type")?checkbox("types",1,$K["types"],'User types'):"").(support("routine")?checkbox("routines",1,$K["routines"],'Routines'):"").(support("event")?checkbox("events",1,$K["events"],'Events'):"")),"<tr><th>".'Tables'."<td>".html_select('table_style',$Di,$K["table_style"]).checkbox("auto_increment",1,$K["auto_increment"],'Auto Increment').(support("trigger")?checkbox("triggers",1,$K["triggers"],'Triggers'):""),"<tr><th>".'Data'."<td>".html_select('data_style',$Mb,$K["data_style"]),'</table>
<p><input type="submit" value="Export">
',input_token(),'
<table>
',script("qsl('table').onclick = dumpClick;");$Tg=array();if(DB!=""){$Za=($a!=""?"":" checked");echo"<thead><tr>","<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Za>".'Tables'."</label>".script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);",""),"<th style='text-align: right;'><label class='block'>".'Data'."<input type='checkbox' id='check-data'$Za></label>".script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);",""),"</thead>\n";$Nj="";$Fi=tables_list();foreach($Fi
as$B=>$U){$Sg=preg_replace('~_.*~','',$B);$Za=($a==""||$a==(substr($a,-1)=="%"?"$Sg%":$B));$Wg="<tr><td>".checkbox("tables[]",$B,$Za,$B,"","block");if($U!==null&&!preg_match('~table~i',$U))$Nj
.="$Wg\n";else
echo"$Wg<td align='right'><label class='block'><span id='Rows-".h($B)."'></span>".checkbox("data[]",$B,$Za)."</label>\n";$Tg[$Sg]++;}echo$Nj;if($Fi)echo
script("ajaxSetHtml('".js_escape(ME)."script=db');");}else{echo"<thead><tr><th style='text-align: left;'>","<label class='block'><input type='checkbox' id='check-databases'".($a==""?" checked":"").">".'Database'."</label>",script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);",""),"</thead>\n";$i=adminer()->databases();if($i){foreach($i
as$j){if(!information_schema($j)){$Sg=preg_replace('~_.*~','',$j);echo"<tr><td>".checkbox("databases[]",$j,$a==""||$a=="$Sg%",$j,"","block")."\n";$Tg[$Sg]++;}}}else
echo"<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";}echo'</table>
</form>
';$bd=true;foreach($Tg
as$x=>$X){if($x!=""&&$X>1){echo($bd?"<p>":" ")."<a href='".h(ME)."dump=".urlencode("$x%")."'>".h($x)."</a>";$bd=false;}}}elseif(isset($_GET["privileges"])){page_header('Privileges');echo'<p class="links"><a href="'.h(ME).'user=">'.'Create user'."</a>";$I=connection()->query("SELECT User, Host FROM mysql.".(DB==""?"user":"db WHERE ".q(DB)." LIKE Db")." ORDER BY Host, User");$ud=$I;if(!$I)$I=connection()->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");echo"<form action=''><p>\n";hidden_fields_get();echo
input_hidden("db",DB),($ud?"":input_hidden("grant")),"<table class='odds'>\n","<thead><tr><th>".'Username'."<th>".'Server'."<th></thead>\n";while($K=$I->fetch_assoc())echo'<tr><td>'.h($K["User"])."<td>".h($K["Host"]).'<td><a href="'.h(ME.'user='.urlencode($K["User"]).'&host='.urlencode($K["Host"])).'">'.'Edit'."</a>\n";if(!$ud||DB!="")echo"<tr><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='".'Edit'."'>\n";echo"</table>\n","</form>\n";}elseif(isset($_GET["sql"])){if(!$l&&$_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers("sql");if($_POST["format"]=="sql")echo"$_POST[query]\n";else{adminer()->dumpTable("","");adminer()->dumpData("","table",$_POST["query"]);adminer()->dumpFooter();}exit;}restart_session();$Kd=&get_session("queries");$Jd=&$Kd[DB];if(!$l&&$_POST["clear"]){$Jd=array();redirect(remove_from_uri("history"));}stop_session();page_header((isset($_GET["import"])?'Import':'SQL command'),$l);$Qe='--'.(JUSH=='sql'?' ':'');if(!$l&&$_POST){$q=false;if(!isset($_GET["import"]))$H=$_POST["query"];elseif($_POST["webfile"]){$ji=adminer()->importServerPath();$q=@fopen((file_exists($ji)?$ji:"compress.zlib://$ji.gz"),"rb");$H=($q?fread($q,1e6):false);}else$H=get_file("sql_file",true,";");if(is_string($H)){if(function_exists('memory_get_usage')&&($kf=ini_bytes("memory_limit"))!="-1")@ini_set("memory_limit",max($kf,strval(2*strlen($H)+memory_get_usage()+8e6)));if($H!=""&&strlen($H)<1e6){$dh=$H.(preg_match("~;[ \t\r\n]*\$~",$H)?"":";");if(!$Jd||first(end($Jd))!=$dh){restart_session();$Jd[]=array($dh,time());set_session("queries",$Kd);stop_session();}}$gi="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|$Qe)[^\n]*\n?|--\r?\n)";$Xb=";";$C=0;$wc=true;$g=connect();if($g&&DB!=""){$g->select_db(DB);if($_GET["ns"]!="")set_schema($_GET["ns"],$g);}$nb=0;$Dc=array();$wg='[\'"'.(JUSH=="sql"?'`#':(JUSH=="sqlite"?'`[':(JUSH=="mssql"?'[':''))).']|/\*|'.$Qe.'|$'.(JUSH=="pgsql"?'|\$([a-zA-Z]\w*)?\$':'');$aj=microtime(true);$ma=get_settings("adminer_import");while($H!=""){if(!$C&&preg_match("~^$gi*+DELIMITER\\s+(\\S+)~i",$H,$A)){$Xb=preg_quote($A[1]);$H=substr($H,strlen($A[0]));}elseif(!$C&&JUSH=='pgsql'&&preg_match("~^($gi*+COPY\\s+)[^;]+\\s+FROM\\s+stdin;~i",$H,$A)){$Xb="\n\\\\\\.\r?\n";$C=strlen($A[0]);}else{preg_match("($Xb\\s*|$wg)",$H,$A,PREG_OFFSET_CAPTURE,$C);list($nd,$Ng)=$A[0];if(!$nd&&$q&&!feof($q))$H
.=fread($q,1e5);else{if(!$nd&&rtrim($H)=="")break;$C=$Ng+strlen($nd);if($nd&&!preg_match("(^$Xb)",$nd)){$Ra=driver()->hasCStyleEscapes()||(JUSH=="pgsql"&&($Ng>0&&strtolower($H[$Ng-1])=="e"));$Gg=($nd=='/*'?'\*/':($nd=='['?']':(preg_match("~^$Qe|^#~",$nd)?"\n":preg_quote($nd).($Ra?'|\\\\.':''))));while(preg_match("($Gg|\$)s",$H,$A,PREG_OFFSET_CAPTURE,$C)){$Dh=$A[0][0];if(!$Dh&&$q&&!feof($q))$H
.=fread($q,1e5);else{$C=$A[0][1]+strlen($Dh);if(!$Dh||$Dh[0]!="\\")break;}}}else{$wc=false;$dh=substr($H,0,$Ng+($Xb[0]=="\n"?3:0));$nb++;$Wg="<pre id='sql-$nb'><code class='jush-".JUSH."'>".adminer()->sqlCommandQuery($dh)."</code></pre>\n";if(JUSH=="sqlite"&&preg_match("~^$gi*+ATTACH\\b~i",$dh,$A)){echo$Wg,"<p class='error'>".'ATTACH queries are not supported.'."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break;}else{if(!$_POST["only_errors"]){echo$Wg;ob_flush();flush();}$oi=microtime(true);if(connection()->multi_query($dh)&&$g&&preg_match("~^$gi*+USE\\b~i",$dh))$g->query($dh);do{$I=connection()->store_result();if(connection()->error){echo($_POST["only_errors"]?$Wg:""),"<p class='error'>".'Error in query'.(connection()->errno?" (".connection()->errno.")":"").": ".error()."\n";$Dc[]=" <a href='#sql-$nb'>$nb</a>";if($_POST["error_stops"])break
2;}else{$Pi=" <span class='time'>(".format_time($oi).")</span>".(strlen($dh)<1000?" <a href='".h(ME)."sql=".urlencode(trim($dh))."'>".'Edit'."</a>":"");$oa=connection()->affected_rows;$Qj=($_POST["only_errors"]?"":driver()->warnings());$Rj="warnings-$nb";if($Qj)$Pi
.=", <a href='#$Rj'>".'Warnings'."</a>".script("qsl('a').onclick = partial(toggle, '$Rj');","");$Lc=null;$hg=null;$Mc="explain-$nb";if(is_object($I)){$z=$_POST["limit"];$hg=print_select_result($I,$g,array(),$z);if(!$_POST["only_errors"]){echo"<form action='' method='post'>\n";$If=$I->num_rows;echo"<p class='sql-footer'>".($If?($z&&$If>$z?sprintf('%d / ',$z):"").lang_format(array('%d row','%d rows'),$If):""),$Pi;if($g&&preg_match("~^($gi|\\()*+SELECT\\b~i",$dh)&&($Lc=explain($g,$dh)))echo", <a href='#$Mc'>Explain</a>".script("qsl('a').onclick = partial(toggle, '$Mc');","");$t="export-$nb";echo", <a href='#$t'>".'Export'."</a>".script("qsl('a').onclick = partial(toggle, '$t');","")."<span id='$t' class='hidden'>: ".html_select("output",adminer()->dumpOutput(),$ma["output"])." ".html_select("format",adminer()->dumpFormat(),$ma["format"]).input_hidden("query",$dh)."<input type='submit' name='export' value='".'Export'."'>".input_token()."</span>\n"."</form>\n";}}else{if(preg_match("~^$gi*+(CREATE|DROP|ALTER)$gi++(DATABASE|SCHEMA)\\b~i",$dh)){restart_session();set_session("dbs",null);stop_session();}if(!$_POST["only_errors"])echo"<p class='message' title='".h(connection()->info)."'>".lang_format(array('Query executed OK, %d row affected.','Query executed OK, %d rows affected.'),$oa)."$Pi\n";}echo($Qj?"<div id='$Rj' class='hidden'>\n$Qj</div>\n":"");if($Lc){echo"<div id='$Mc' class='hidden explain'>\n";print_select_result($Lc,$g,$hg);echo"</div>\n";}}$oi=microtime(true);}while(connection()->next_result());}$H=substr($H,$C);$C=0;}}}}if($wc)echo"<p class='message'>".'No commands to execute.'."\n";elseif($_POST["only_errors"])echo"<p class='message'>".lang_format(array('%d query executed OK.','%d queries executed OK.'),$nb-count($Dc))," <span class='time'>(".format_time($aj).")</span>\n";elseif($Dc&&$nb>1)echo"<p class='error'>".'Error in query'.": ".implode("",$Dc)."\n";}else
echo"<p class='error'>".upload_error($H)."\n";}echo'
<form action="" method="post" enctype="multipart/form-data" id="form">
';$Jc="<input type='submit' value='".'Execute'."' title='Ctrl+Enter'>";if(!isset($_GET["import"])){$dh=$_GET["sql"];if($_POST)$dh=$_POST["query"];elseif($_GET["history"]=="all")$dh=$Jd;elseif($_GET["history"]!="")$dh=idx($Jd[$_GET["history"]],0);echo"<p>";textarea("query",$dh,20);echo
script(($_POST?"":"qs('textarea').focus();\n")."qs('#form').onsubmit = partial(sqlSubmit, qs('#form'), '".js_escape(remove_from_uri("sql|limit|error_stops|only_errors|history"))."');"),"<p>";adminer()->sqlPrintAfter();echo"$Jc\n",'Limit rows'.": <input type='number' name='limit' class='size' value='".h($_POST?$_POST["limit"]:$_GET["limit"])."'>\n";}else{$_d=(extension_loaded("zlib")?"[.gz]":"");echo"<fieldset><legend>".'File upload'."</legend><div>",file_input("SQL$_d: <input type='file' name='sql_file[]' multiple>\n$Jc"),"</div></fieldset>\n";$Vd=adminer()->importServerPath();if($Vd)echo"<fieldset><legend>".'From server'."</legend><div>",sprintf('Webserver file %s',"<code>".h($Vd)."$_d</code>"),' <input type="submit" name="webfile" value="'.'Run file'.'">',"</div></fieldset>\n";echo"<p>";}echo
checkbox("error_stops",1,($_POST?$_POST["error_stops"]:isset($_GET["import"])||$_GET["error_stops"]),'Stop on error')."\n",checkbox("only_errors",1,($_POST?$_POST["only_errors"]:isset($_GET["import"])||$_GET["only_errors"]),'Show only errors')."\n",input_token();if(!isset($_GET["import"])&&$Jd){print_fieldset("history",'History',$_GET["history"]!="");for($X=end($Jd);$X;$X=prev($Jd)){$x=key($Jd);list($dh,$Pi,$rc)=$X;echo'<a href="'.h(ME."sql=&history=$x").'">'.'Edit'."</a>"." <span class='time' title='".@date('Y-m-d',$Pi)."'>".@date("H:i:s",$Pi)."</span>"." <code class='jush-".JUSH."'>".shorten_utf8(ltrim(str_replace("\n"," ",str_replace("\r","",preg_replace("~^(#|$Qe).*~m",'',$dh)))),80,"</code>").($rc?" <span class='time'>($rc)</span>":"")."<br>\n";}echo"<input type='submit' name='clear' value='".'Clear'."'>\n","<a href='".h(ME."sql=&history=all")."'>".'Edit all'."</a>\n","</div></fieldset>\n";}echo'</form>
';}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$n=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$n):""):where($_GET,$n));$wj=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($n
as$B=>$m){if(!isset($m["privileges"][$wj?"update":"insert"])||adminer()->fieldName($m)==""||$m["generated"])unset($n[$B]);}if($_POST&&!$l&&!isset($_GET["select"])){$Se=$_POST["referer"];if($_POST["insert"])$Se=($wj?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$Se))$Se=ME."select=".urlencode($a);$w=indexes($a);$rj=unique_array($_GET["where"],$w);$gh="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($Se,'Item has been deleted.',driver()->delete($a,$gh,$rj?0:1));else{$O=array();foreach($n
as$B=>$m){$X=process_input($m);if($X!==false&&$X!==null)$O[idf_escape($B)]=$X;}if($wj){if(!$O)redirect($Se);queries_redirect($Se,'Item has been updated.',driver()->update($a,$O,$gh,$rj?0:1));if(is_ajax()){page_headers();page_messages($l);exit;}}else{$I=driver()->insert($a,$O);$Je=($I?last_id($I):0);queries_redirect($Se,sprintf('Item%s has been inserted.',($Je?" $Je":"")),$I);}}}$K=null;if($_POST["save"])$K=(array)$_POST["fields"];elseif($Z){$M=array();foreach($n
as$B=>$m){if(isset($m["privileges"]["select"])){$wa=($_POST["clone"]&&$m["auto_increment"]?"''":convert_field($m));$M[]=($wa?"$wa AS ":"").idf_escape($B);}}$K=array();if(!support("table"))$M=array("*");if($M){$I=driver()->select($a,$M,array($Z),$M,array(),(isset($_GET["select"])?2:1));if(!$I)$l=error();else{$K=$I->fetch_assoc();if(!$K)$K=false;}if(isset($_GET["select"])&&(!$K||$I->fetch_assoc()))$K=null;}}if(!support("table")&&!$n){if(!$Z){$I=driver()->select($a,array("*"),array(),array("*"));$K=($I?$I->fetch_assoc():false);if(!$K)$K=array(driver()->primary=>"");}if($K){foreach($K
as$x=>$X){if(!$Z)$K[$x]=null;$n[$x]=array("field"=>$x,"null"=>($x!=driver()->primary),"auto_increment"=>($x==driver()->primary));}}}edit_form($a,$n,$K,$wj,$l);}elseif(isset($_GET["create"])){$a=$_GET["create"];$Ag=driver()->partitionBy;$Dg=($Ag?driver()->partitionsInfo($a):array());$mh=referencable_primary($a);$ld=array();foreach($mh
as$_i=>$m)$ld[str_replace("`","``",$_i)."`".str_replace("`","``",$m["field"])]=$_i;$kg=array();$S=array();if($a!=""){$kg=fields($a);$S=table_status1($a);if(count($S)<2)$l='No tables.';}$K=$_POST;$K["fields"]=(array)$K["fields"];if($K["auto_increment_col"])$K["fields"][$K["auto_increment_col"]]["auto_increment"]=true;if($_POST)save_settings(array("comments"=>$_POST["comments"],"defaults"=>$_POST["defaults"]));if($_POST&&!process_fields($K["fields"])&&!$l){if($_POST["drop"])queries_redirect(substr(ME,0,-1),'Table has been dropped.',drop_tables(array($a)));else{$n=array();$sa=array();$Bj=false;$jd=array();$jg=reset($kg);$qa=" FIRST";foreach($K["fields"]as$x=>$m){$p=$ld[$m["type"]];$lj=($p!==null?$mh[$p]:$m);if($m["field"]!=""){if(!$m["generated"])$m["default"]=null;$bh=process_field($m,$lj);$sa[]=array($m["orig"],$bh,$qa);if(!$jg||$bh!==process_field($jg,$jg)){$n[]=array($m["orig"],$bh,$qa);if($m["orig"]!=""||$qa)$Bj=true;}if($p!==null)$jd[idf_escape($m["field"])]=($a!=""&&JUSH!="sqlite"?"ADD":" ").format_foreign_key(array('table'=>$ld[$m["type"]],'source'=>array($m["field"]),'target'=>array($lj["field"]),'on_delete'=>$m["on_delete"],));$qa=" AFTER ".idf_escape($m["field"]);}elseif($m["orig"]!=""){$Bj=true;$n[]=array($m["orig"]);}if($m["orig"]!=""){$jg=next($kg);if(!$jg)$qa="";}}$E=array();if(in_array($K["partition_by"],$Ag)){foreach($K
as$x=>$X){if(preg_match('~^partition~',$x))$E[$x]=$X;}foreach($E["partition_names"]as$x=>$B){if($B==""){unset($E["partition_names"][$x]);unset($E["partition_values"][$x]);}}$E["partition_names"]=array_values($E["partition_names"]);$E["partition_values"]=array_values($E["partition_values"]);if($E==$Dg)$E=array();}elseif(preg_match("~partitioned~",$S["Create_options"]))$E=null;$lf='Table has been altered.';if($a==""){cookie("adminer_engine",$K["Engine"]);$lf='Table has been created.';}$B=trim($K["name"]);queries_redirect(ME.(support("table")?"table=":"select=").urlencode($B),$lf,alter_table($a,$B,(JUSH=="sqlite"&&($Bj||$jd)?$sa:$n),$jd,($K["Comment"]!=$S["Comment"]?$K["Comment"]:null),($K["Engine"]&&$K["Engine"]!=$S["Engine"]?$K["Engine"]:""),($K["Collation"]&&$K["Collation"]!=$S["Collation"]?$K["Collation"]:""),($K["Auto_increment"]!=""?number($K["Auto_increment"]):""),$E));}}page_header(($a!=""?'Alter table':'Create table'),$l,array("table"=>$a),h($a));if(!$_POST){$nj=driver()->types();$K=array("Engine"=>$_COOKIE["adminer_engine"],"fields"=>array(array("field"=>"","type"=>(isset($nj["int"])?"int":(isset($nj["integer"])?"integer":"")),"on_update"=>"")),"partition_names"=>array(""),);if($a!=""){$K=$S;$K["name"]=$a;$K["fields"]=array();if(!$_GET["auto_increment"])$K["Auto_increment"]="";foreach($kg
as$m){$m["generated"]=$m["generated"]?:(isset($m["default"])?"DEFAULT":"");$K["fields"][]=$m;}if($Ag){$K+=$Dg;$K["partition_names"][]="";$K["partition_values"][]="";}}}$jb=collations();if(is_array(reset($jb)))$jb=call_user_func_array('array_merge',array_values($jb));$yc=driver()->engines();foreach($yc
as$xc){if(!strcasecmp($xc,$K["Engine"])){$K["Engine"]=$xc;break;}}echo'
<form action="" method="post" id="form">
<p>
';if(support("columns")||$a==""){echo'Table name'.": <input name='name'".($a==""&&!$_POST?" autofocus":"")." data-maxlength='64' value='".h($K["name"])."' autocapitalize='off'>\n",($yc?html_select("Engine",array(""=>"(".'engine'.")")+$yc,$K["Engine"]).on_help("event.target.value",1).script("qsl('select').onchange = helpClose;")."\n":"");if($jb)echo"<datalist id='collations'>".optionlist($jb)."</datalist>\n",(preg_match("~sqlite|mssql~",JUSH)?"":"<input list='collations' name='Collation' value='".h($K["Collation"])."' placeholder='(".'collation'.")'>\n");echo"<input type='submit' value='".'Save'."'>\n";}if(support("columns")){echo"<div class='scrollable'>\n","<table id='edit-fields' class='nowrap'>\n";edit_fields($K["fields"],$jb,"TABLE",$ld);echo"</table>\n",script("editFields();"),"</div>\n<p>\n",'Auto Increment'.": <input type='number' name='Auto_increment' class='size' value='".h($K["Auto_increment"])."'>\n",checkbox("defaults",1,($_POST?$_POST["defaults"]:get_setting("defaults")),'Default values',"columnShow(this.checked, 5)","jsonly");$qb=($_POST?$_POST["comments"]:get_setting("comments"));echo(support("comment")?checkbox("comments",1,$qb,'Comment',"editingCommentsClick(this, true);","jsonly").' '.(preg_match('~\n~',$K["Comment"])?"<textarea name='Comment' rows='2' cols='20'".($qb?"":" class='hidden'").">".h($K["Comment"])."</textarea>":'<input name="Comment" value="'.h($K["Comment"]).'" data-maxlength="'.(min_version(5.5)?2048:60).'"'.($qb?"":" class='hidden'").'>'):''),'<p>
<input type="submit" value="Save">
';}echo'
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));if($Ag&&(JUSH=='sql'||$a=="")){$Bg=preg_match('~RANGE|LIST~',$K["partition_by"]);print_fieldset("partition",'Partition by',$K["partition_by"]);echo"<p>".html_select("partition_by",array_merge(array(""),$Ag),$K["partition_by"]).on_help("event.target.value.replace(/./, 'PARTITION BY \$&')",1).script("qsl('select').onchange = partitionByChange;"),"(<input name='partition' value='".h($K["partition"])."'>)\n",'Partitions'.": <input type='number' name='partitions' class='size".($Bg||!$K["partition_by"]?" hidden":"")."' value='".h($K["partitions"])."'>\n","<table id='partition-table'".($Bg?"":" class='hidden'").">\n","<thead><tr><th>".'Partition name'."<th>".'Values'."</thead>\n";foreach($K["partition_names"]as$x=>$X)echo'<tr>','<td><input name="partition_names[]" value="'.h($X).'" autocapitalize="off">',($x==count($K["partition_names"])-1?script("qsl('input').oninput = partitionNameChange;"):''),'<td><input name="partition_values[]" value="'.h(idx($K["partition_values"],$x)).'">';echo"</table>\n</div></fieldset>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["indexes"])){$a=$_GET["indexes"];$de=array("PRIMARY","UNIQUE","INDEX");$S=table_status1($a,true);$ae=driver()->indexAlgorithms($S);if(preg_match('~MyISAM|M?aria'.(min_version(5.6,'10.0.5')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="FULLTEXT";if(preg_match('~MyISAM|M?aria'.(min_version(5.7,'10.2.2')?'|InnoDB':'').'~i',$S["Engine"]))$de[]="SPATIAL";$w=indexes($a);$n=fields($a);$G=array();if(JUSH=="mongo"){$G=$w["_id_"];unset($de[0]);unset($w["_id_"]);}$K=$_POST;if($K)save_settings(array("index_options"=>$K["options"]));if($_POST&&!$l&&!$_POST["add"]&&!$_POST["drop_col"]){$b=array();foreach($K["indexes"]as$v){$B=$v["name"];if(in_array($v["type"],$de)){$e=array();$Oe=array();$ac=array();$be=(support("partial_indexes")?$v["partial"]:"");$Zd=(in_array($v["algorithm"],$ae)?$v["algorithm"]:"");$O=array();ksort($v["columns"]);foreach($v["columns"]as$x=>$d){if($d!=""){$y=idx($v["lengths"],$x);$Yb=idx($v["descs"],$x);$O[]=($n[$d]?idf_escape($d):$d).($y?"(".(+$y).")":"").($Yb?" DESC":"");$e[]=$d;$Oe[]=($y?:null);$ac[]=$Yb;}}$Kc=$w[$B];if($Kc){ksort($Kc["columns"]);ksort($Kc["lengths"]);ksort($Kc["descs"]);if($v["type"]==$Kc["type"]&&array_values($Kc["columns"])===$e&&(!$Kc["lengths"]||array_values($Kc["lengths"])===$Oe)&&array_values($Kc["descs"])===$ac&&$Kc["partial"]==$be&&(!$ae||$Kc["algorithm"]==$Zd)){unset($w[$B]);continue;}}if($e)$b[]=array($v["type"],$B,$O,$Zd,$be);}}foreach($w
as$B=>$Kc)$b[]=array($Kc["type"],$B,"DROP");if(!$b)redirect(ME."table=".urlencode($a));queries_redirect(ME."table=".urlencode($a),'Indexes have been altered.',alter_indexes($a,$b));}page_header('Indexes',$l,array("table"=>$a),h($a));$Yc=array_keys($n);if($_POST["add"]){foreach($K["indexes"]as$x=>$v){if($v["columns"][count($v["columns"])]!="")$K["indexes"][$x]["columns"][]="";}$v=end($K["indexes"]);if($v["type"]||array_filter($v["columns"],'strlen'))$K["indexes"][]=array("columns"=>array(1=>""));}if(!$K){foreach($w
as$x=>$v){$w[$x]["name"]=$x;$w[$x]["columns"][]="";}$w[]=array("columns"=>array(1=>""));$K["indexes"]=$w;}$Oe=(JUSH=="sql"||JUSH=="mssql");$ai=($_POST?$_POST["options"]:get_setting("index_options"));echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap">
<thead><tr>
<th id="label-type">Index Type
';$Td=" class='idxopts".($ai?"":" hidden")."'";if($ae)echo"<th id='label-algorithm'$Td>".'Algorithm'.doc_link(array('sql'=>'create-index.html#create-index-storage-engine-index-types','mariadb'=>'storage-engine-index-types/','pgsql'=>'indexes-types.html',));echo'<th><input type="submit" class="wayoff">','Columns'.($Oe?"<span$Td> (".'length'.")</span>":"");if($Oe||support("descidx"))echo
checkbox("options",1,$ai,'Options',"indexOptionsShow(this.checked)","jsonly")."\n";echo'<th id="label-name">Name
';if(support("partial_indexes"))echo"<th id='label-condition'$Td>".'Condition';echo'<th><noscript>',icon("plus","add[0]","+",'Add next'),'</noscript>
</thead>
';if($G){echo"<tr><td>PRIMARY<td>";foreach($G["columns"]as$x=>$d)echo
select_input(" disabled",$Yc,$d),"<label><input disabled type='checkbox'>".'descending'."</label> ";echo"<td><td>\n";}$ze=1;foreach($K["indexes"]as$v){if(!$_POST["drop_col"]||$ze!=key($_POST["drop_col"])){echo"<tr><td>".html_select("indexes[$ze][type]",array(-1=>"")+$de,$v["type"],($ze==count($K["indexes"])?"indexesAddRow.call(this);":""),"label-type");if($ae)echo"<td$Td>".html_select("indexes[$ze][algorithm]",array_merge(array(""),$ae),$v['algorithm'],"label-algorithm");echo"<td>";ksort($v["columns"]);$s=1;foreach($v["columns"]as$x=>$d){echo"<span>".select_input(" name='indexes[$ze][columns][$s]' title='".'Column'."'",($n&&($d==""||$n[$d])?array_combine($Yc,$Yc):array()),$d,"partial(".($s==count($v["columns"])?"indexesAddColumn":"indexesChangeColumn").", '".js_escape(JUSH=="sql"?"":$_GET["indexes"]."_")."')"),"<span$Td>",($Oe?"<input type='number' name='indexes[$ze][lengths][$s]' class='size' value='".h(idx($v["lengths"],$x))."' title='".'Length'."'>":""),(support("descidx")?checkbox("indexes[$ze][descs][$s]",1,idx($v["descs"],$x),'descending'):""),"</span> </span>";$s++;}echo"<td><input name='indexes[$ze][name]' value='".h($v["name"])."' autocapitalize='off' aria-labelledby='label-name'>\n";if(support("partial_indexes"))echo"<td$Td><input name='indexes[$ze][partial]' value='".h($v["partial"])."' autocapitalize='off' aria-labelledby='label-condition'>\n";echo"<td>".icon("cross","drop_col[$ze]","x",'Remove').script("qsl('button').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");}$ze++;}echo'</table>
</div>
<p>
<input type="submit" value="Save">
',input_token(),'</form>
';}elseif(isset($_GET["database"])){$K=$_POST;if($_POST&&!$l&&!$_POST["add"]){$B=trim($K["name"]);if($_POST["drop"]){$_GET["db"]="";queries_redirect(remove_from_uri("db|database"),'Database has been dropped.',drop_databases(array(DB)));}elseif(DB!==$B){if(DB!=""){$_GET["db"]=$B;queries_redirect(preg_replace('~\bdb=[^&]*&~','',ME)."db=".urlencode($B),'Database has been renamed.',rename_database($B,$K["collation"]));}else{$i=explode("\n",str_replace("\r","",$B));$ti=true;$Ie="";foreach($i
as$j){if(count($i)==1||$j!=""){if(!create_database($j,$K["collation"]))$ti=false;$Ie=$j;}}restart_session();set_session("dbs",null);queries_redirect(ME."db=".urlencode($Ie),'Database has been created.',$ti);}}else{if(!$K["collation"])redirect(substr(ME,0,-1));query_redirect("ALTER DATABASE ".idf_escape($B).(preg_match('~^[a-z0-9_]+$~i',$K["collation"])?" COLLATE $K[collation]":""),substr(ME,0,-1),'Database has been altered.');}}page_header(DB!=""?'Alter database':'Create database',$l,array(),h(DB));$jb=collations();$B=DB;if($_POST)$B=$K["name"];elseif(DB!="")$K["collation"]=db_collation(DB,$jb);elseif(JUSH=="sql"){foreach(get_vals("SHOW GRANTS")as$ud){if(preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~',$ud,$A)&&$A[1]){$B=stripcslashes(idf_unescape("`$A[2]`"));break;}}}echo'
<form action="" method="post">
<p>
',($_POST["add"]||strpos($B,"\n")?'<textarea autofocus name="name" rows="10" cols="40">'.h($B).'</textarea><br>':'<input name="name" autofocus value="'.h($B).'" data-maxlength="64" autocapitalize="off">')."\n".($jb?html_select("collation",array(""=>"(".'collation'.")")+$jb,$K["collation"]).doc_link(array('sql'=>"charset-charsets.html",'mariadb'=>"supported-character-sets-and-collations/",'mssql'=>"relational-databases/system-functions/sys-fn-helpcollations-transact-sql",)):""),'<input type="submit" value="Save">
';if(DB!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',DB))."\n";elseif(!$_POST["add"]&&$_GET["db"]=="")echo
icon("plus","add[0]","+",'Add next')."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["scheme"])){$K=$_POST;if($_POST&&!$l){$_=preg_replace('~ns=[^&]*&~','',ME)."ns=";if($_POST["drop"])query_redirect("DROP SCHEMA ".idf_escape($_GET["ns"]),$_,'Schema has been dropped.');else{$B=trim($K["name"]);$_
.=urlencode($B);if($_GET["ns"]=="")query_redirect("CREATE SCHEMA ".idf_escape($B),$_,'Schema has been created.');elseif($_GET["ns"]!=$B)query_redirect("ALTER SCHEMA ".idf_escape($_GET["ns"])." RENAME TO ".idf_escape($B),$_,'Schema has been altered.');else
redirect($_);}}page_header($_GET["ns"]!=""?'Alter schema':'Create schema',$l);if(!$K)$K["name"]=$_GET["ns"];echo'
<form action="" method="post">
<p><input name="name" autofocus value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($_GET["ns"]!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$_GET["ns"]))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["call"])){$ba=($_GET["name"]?:$_GET["call"]);page_header('Call'.": ".h($ba),$l);$_h=routine($_GET["call"],(isset($_GET["callf"])?"FUNCTION":"PROCEDURE"));$Wd=array();$pg=array();foreach($_h["fields"]as$s=>$m){if(substr($m["inout"],-3)=="OUT"&&JUSH=='sql')$pg[$s]="@".idf_escape($m["field"])." AS ".idf_escape($m["field"]);if(!$m["inout"]||substr($m["inout"],0,2)=="IN")$Wd[]=$s;}if(!$l&&$_POST){$Sa=array();foreach($_h["fields"]as$x=>$m){$X="";if(in_array($x,$Wd)){$X=process_input($m);if($X===false)$X="''";if(isset($pg[$x]))connection()->query("SET @".idf_escape($m["field"])." = $X");}if(isset($pg[$x]))$Sa[]="@".idf_escape($m["field"]);elseif(in_array($x,$Wd))$Sa[]=$X;}$H=(isset($_GET["callf"])?"SELECT ":"CALL ").($_h["returns"]["type"]=="record"?"* FROM ":"").table($ba)."(".implode(", ",$Sa).")";$oi=microtime(true);$I=connection()->multi_query($H);$oa=connection()->affected_rows;echo
adminer()->selectQuery($H,$oi,!$I);if(!$I)echo"<p class='error'>".error()."\n";else{$g=connect();if($g)$g->select_db(DB);do{$I=connection()->store_result();if(is_object($I))print_select_result($I,$g);else
echo"<p class='message'>".lang_format(array('Routine has been called, %d row affected.','Routine has been called, %d rows affected.'),$oa)." <span class='time'>".@date("H:i:s")."</span>\n";}while(connection()->next_result());if($pg)print_select_result(connection()->query("SELECT ".implode(", ",$pg)));}}echo'
<form action="" method="post">
';if($Wd){echo"<table class='layout'>\n";foreach($Wd
as$x){$m=$_h["fields"][$x];$B=$m["field"];echo"<tr><th>".adminer()->fieldName($m);$Y=idx($_POST["fields"],$B);if($Y!=""){if($m["type"]=="set")$Y=implode(",",$Y);}input($m,$Y,idx($_POST["function"],$B,""));echo"\n";}echo"</table>\n";}echo'<p>
<input type="submit" value="Call">
',input_token(),'</form>

<pre>
';function
pre_tr($Dh){return
preg_replace('~^~m','<tr>',preg_replace('~\|~','<td>',preg_replace('~\|$~m',"",rtrim($Dh))));}$R='(\+--[-+]+\+\n)';$K='(\| .* \|\n)';echo
preg_replace_callback("~^$R?$K$R?($K*)$R?~m",function($A){$cd=pre_tr($A[2]);return"<table>\n".($A[1]?"<thead>$cd</thead>\n":$cd).pre_tr($A[4])."\n</table>";},preg_replace('~(\n(    -|mysql)&gt; )(.+)~',"\\1<code class='jush-sql'>\\3</code>",preg_replace('~(.+)\n---+\n~',"<b>\\1</b>\n",h($_h['comment']))));echo'</pre>
';}elseif(isset($_GET["foreign"])){$a=$_GET["foreign"];$B=$_GET["name"];$K=$_POST;if($_POST&&!$l&&!$_POST["add"]&&!$_POST["change"]&&!$_POST["change-js"]){if(!$_POST["drop"]){$K["source"]=array_filter($K["source"],'strlen');ksort($K["source"]);$Ii=array();foreach($K["source"]as$x=>$X)$Ii[$x]=$K["target"][$x];$K["target"]=$Ii;}if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(" $B"=>($K["drop"]?"":" ".format_foreign_key($K))));else{$b="ALTER TABLE ".table($a);$I=($B==""||queries("$b DROP ".(JUSH=="sql"?"FOREIGN KEY ":"CONSTRAINT ").idf_escape($B)));if(!$K["drop"])$I=queries("$b ADD".format_foreign_key($K));}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Foreign key has been dropped.':($B!=""?'Foreign key has been altered.':'Foreign key has been created.')),$I);if(!$K["drop"])$l='Source and target columns must have the same data type, there must be an index on the target columns and referenced data must exist.';}page_header('Foreign key',$l,array("table"=>$a),h($a));if($_POST){ksort($K["source"]);if($_POST["add"])$K["source"][]="";elseif($_POST["change"]||$_POST["change-js"])$K["target"]=array();}elseif($B!=""){$ld=foreign_keys($a);$K=$ld[$B];$K["source"][]="";}else{$K["table"]=$a;$K["source"]=array("");}echo'
<form action="" method="post">
';$fi=array_keys(fields($a));if($K["db"]!="")connection()->select_db($K["db"]);if($K["ns"]!=""){$lg=get_schema();set_schema($K["ns"]);}$lh=array_keys(array_filter(table_status('',true),'Adminer\fk_support'));$Ii=array_keys(fields(in_array($K["table"],$lh)?$K["table"]:reset($lh)));$Vf="this.form['change-js'].value = '1'; this.form.submit();";echo"<p><label>".'Target table'.": ".html_select("table",$lh,$K["table"],$Vf)."</label>\n";if(support("scheme")){$Gh=array_filter(adminer()->schemas(),function($Fh){return!preg_match('~^information_schema$~i',$Fh);});echo"<label>".'Schema'.": ".html_select("ns",$Gh,$K["ns"]!=""?$K["ns"]:$_GET["ns"],$Vf)."</label>";if($K["ns"]!="")set_schema($lg);}elseif(JUSH!="sqlite"){$Qb=array();foreach(adminer()->databases()as$j){if(!information_schema($j))$Qb[]=$j;}echo"<label>".'DB'.": ".html_select("db",$Qb,$K["db"]!=""?$K["db"]:$_GET["db"],$Vf)."</label>";}echo
input_hidden("change-js"),'<noscript><p><input type="submit" name="change" value="Change"></noscript>
<table>
<thead><tr><th id="label-source">Source<th id="label-target">Target</thead>
';$ze=0;foreach($K["source"]as$x=>$X){echo"<tr>","<td>".html_select("source[".(+$x)."]",array(-1=>"")+$fi,$X,($ze==count($K["source"])-1?"foreignAddRow.call(this);":""),"label-source"),"<td>".html_select("target[".(+$x)."]",$Ii,idx($K["target"],$x),"","label-target");$ze++;}echo'</table>
<p>
<label>ON DELETE: ',html_select("on_delete",array(-1=>"")+explode("|",driver()->onActions),$K["on_delete"]),'</label>
<label>ON UPDATE: ',html_select("on_update",array(-1=>"")+explode("|",driver()->onActions),$K["on_update"]),'</label>
',doc_link(array('sql'=>"innodb-foreign-key-constraints.html",'mariadb'=>"foreign-keys/",'pgsql'=>"sql-createtable.html#SQL-CREATETABLE-REFERENCES",'mssql'=>"t-sql/statements/create-table-transact-sql",'oracle'=>"SQLRF01111",)),'<p>
<input type="submit" value="Save">
<noscript><p><input type="submit" name="add" value="Add column"></noscript>
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["view"])){$a=$_GET["view"];$K=$_POST;$mg="VIEW";if(JUSH=="pgsql"&&$a!=""){$P=table_status1($a);$mg=strtoupper($P["Engine"]);}if($_POST&&!$l){$B=trim($K["name"]);$wa=" AS\n$K[select]";$Se=ME."table=".urlencode($B);$lf='View has been altered.';$U=($_POST["materialized"]?"MATERIALIZED VIEW":"VIEW");if(!$_POST["drop"]&&$a==$B&&JUSH!="sqlite"&&$U=="VIEW"&&$mg=="VIEW")query_redirect((JUSH=="mssql"?"ALTER":"CREATE OR REPLACE")." VIEW ".table($B).$wa,$Se,$lf);else{$Ki=$B."_adminer_".uniqid();drop_create("DROP $mg ".table($a),"CREATE $U ".table($B).$wa,"DROP $U ".table($B),"CREATE $U ".table($Ki).$wa,"DROP $U ".table($Ki),($_POST["drop"]?substr(ME,0,-1):$Se),'View has been dropped.',$lf,'View has been created.',$a,$B);}}if(!$_POST&&$a!=""){$K=view($a);$K["name"]=$a;$K["materialized"]=($mg!="VIEW");if(!$l)$l=error();}page_header(($a!=""?'Alter view':'Create view'),$l,array("table"=>$a),h($a));echo'
<form action="" method="post">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',(support("materializedview")?" ".checkbox("materialized",1,$K["materialized"],'Materialized view'):""),'<p>';textarea("select",$K["select"]);echo'<p>
<input type="submit" value="Save">
';if($a!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$a));echo
input_token(),'</form>
';}elseif(isset($_GET["event"])){$aa=$_GET["event"];$qe=array("YEAR","QUARTER","MONTH","DAY","HOUR","MINUTE","WEEK","SECOND","YEAR_MONTH","DAY_HOUR","DAY_MINUTE","DAY_SECOND","HOUR_MINUTE","HOUR_SECOND","MINUTE_SECOND");$pi=array("ENABLED"=>"ENABLE","DISABLED"=>"DISABLE","SLAVESIDE_DISABLED"=>"DISABLE ON SLAVE");$K=$_POST;if($_POST&&!$l){if($_POST["drop"])query_redirect("DROP EVENT ".idf_escape($aa),substr(ME,0,-1),'Event has been dropped.');elseif(in_array($K["INTERVAL_FIELD"],$qe)&&isset($pi[$K["STATUS"]])){$Eh="\nON SCHEDULE ".($K["INTERVAL_VALUE"]?"EVERY ".q($K["INTERVAL_VALUE"])." $K[INTERVAL_FIELD]".($K["STARTS"]?" STARTS ".q($K["STARTS"]):"").($K["ENDS"]?" ENDS ".q($K["ENDS"]):""):"AT ".q($K["STARTS"]))." ON COMPLETION".($K["ON_COMPLETION"]?"":" NOT")." PRESERVE";queries_redirect(substr(ME,0,-1),($aa!=""?'Event has been altered.':'Event has been created.'),queries(($aa!=""?"ALTER EVENT ".idf_escape($aa).$Eh.($aa!=$K["EVENT_NAME"]?"\nRENAME TO ".idf_escape($K["EVENT_NAME"]):""):"CREATE EVENT ".idf_escape($K["EVENT_NAME"]).$Eh)."\n".$pi[$K["STATUS"]]." COMMENT ".q($K["EVENT_COMMENT"]).rtrim(" DO\n$K[EVENT_DEFINITION]",";").";"));}}page_header(($aa!=""?'Alter event'.": ".h($aa):'Create event'),$l);if(!$K&&$aa!=""){$L=get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = ".q(DB)." AND EVENT_NAME = ".q($aa));$K=reset($L);}echo'
<form action="" method="post">
<table class="layout">
<tr><th>Name<td><input name="EVENT_NAME" value="',h($K["EVENT_NAME"]),'" data-maxlength="64" autocapitalize="off">
<tr><th title="datetime">Start<td><input name="STARTS" value="',h("$K[EXECUTE_AT]$K[STARTS]"),'">
<tr><th title="datetime">End<td><input name="ENDS" value="',h($K["ENDS"]),'">
<tr><th>Every<td><input type="number" name="INTERVAL_VALUE" value="',h($K["INTERVAL_VALUE"]),'" class="size"> ',html_select("INTERVAL_FIELD",$qe,$K["INTERVAL_FIELD"]),'<tr><th>Status<td>',html_select("STATUS",$pi,$K["STATUS"]),'<tr><th>Comment<td><input name="EVENT_COMMENT" value="',h($K["EVENT_COMMENT"]),'" data-maxlength="64">
<tr><th><td>',checkbox("ON_COMPLETION","PRESERVE",$K["ON_COMPLETION"]=="PRESERVE",'On completion preserve'),'</table>
<p>';textarea("EVENT_DEFINITION",$K["EVENT_DEFINITION"]);echo'<p>
<input type="submit" value="Save">
';if($aa!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$aa));echo
input_token(),'</form>
';}elseif(isset($_GET["procedure"])){$ba=($_GET["name"]?:$_GET["procedure"]);$_h=(isset($_GET["function"])?"FUNCTION":"PROCEDURE");$K=$_POST;$K["fields"]=(array)$K["fields"];if($_POST&&!process_fields($K["fields"])&&!$l){$ig=routine($_GET["procedure"],$_h);$Ki="$K[name]_adminer_".uniqid();foreach($K["fields"]as$x=>$m){if($m["field"]=="")unset($K["fields"][$x]);}drop_create("DROP $_h ".routine_id($ba,$ig),create_routine($_h,$K),"DROP $_h ".routine_id($K["name"],$K),create_routine($_h,array("name"=>$Ki)+$K),"DROP $_h ".routine_id($Ki,$K),substr(ME,0,-1),'Routine has been dropped.','Routine has been altered.','Routine has been created.',$ba,$K["name"]);}page_header(($ba!=""?(isset($_GET["function"])?'Alter function':'Alter procedure').": ".h($ba):(isset($_GET["function"])?'Create function':'Create procedure')),$l);if(!$_POST){if($ba=="")$K["language"]="sql";else{$K=routine($_GET["procedure"],$_h);$K["name"]=$ba;}}$jb=get_vals("SHOW CHARACTER SET");sort($jb);$Ah=routine_languages();echo($jb?"<datalist id='collations'>".optionlist($jb)."</datalist>":""),'
<form action="" method="post" id="form">
<p>Name: <input name="name" value="',h($K["name"]),'" data-maxlength="64" autocapitalize="off">
',($Ah?"<label>".'Language'.": ".html_select("language",$Ah,$K["language"])."</label>\n":""),'<input type="submit" value="Save">
<div class="scrollable">
<table class="nowrap">
';edit_fields($K["fields"],$jb,$_h);if(isset($_GET["function"])){echo"<tr><td>".'Return type';edit_type("returns",(array)$K["returns"],$jb,array(),(JUSH=="pgsql"?array("void","trigger"):array()));}echo'</table>
',script("editFields();"),'</div>
<p>';textarea("definition",$K["definition"],20);echo'<p>
<input type="submit" value="Save">
';if($ba!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$ba));echo
input_token(),'</form>
';}elseif(isset($_GET["sequence"])){$da=$_GET["sequence"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);$B=trim($K["name"]);if($_POST["drop"])query_redirect("DROP SEQUENCE ".idf_escape($da),$_,'Sequence has been dropped.');elseif($da=="")query_redirect("CREATE SEQUENCE ".idf_escape($B),$_,'Sequence has been created.');elseif($da!=$B)query_redirect("ALTER SEQUENCE ".idf_escape($da)." RENAME TO ".idf_escape($B),$_,'Sequence has been altered.');else
redirect($_);}page_header($da!=""?'Alter sequence'.": ".h($da):'Create sequence',$l);if(!$K)$K["name"]=$da;echo'
<form action="" method="post">
<p><input name="name" value="',h($K["name"]),'" autocapitalize="off">
<input type="submit" value="Save">
';if($da!="")echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$da))."\n";echo
input_token(),'</form>
';}elseif(isset($_GET["type"])){$ea=$_GET["type"];$K=$_POST;if($_POST&&!$l){$_=substr(ME,0,-1);if($_POST["drop"])query_redirect("DROP TYPE ".idf_escape($ea),$_,'Type has been dropped.');else
query_redirect("CREATE TYPE ".idf_escape(trim($K["name"]))." $K[as]",$_,'Type has been created.');}page_header($ea!=""?'Alter type'.": ".h($ea):'Create type',$l);if(!$K)$K["as"]="AS ";echo'
<form action="" method="post">
<p>
';if($ea!=""){$nj=driver()->types();$Bc=type_values($nj[$ea]);if($Bc)echo"<code class='jush-".JUSH."'>ENUM (".h($Bc).")</code>\n<p>";echo"<input type='submit' name='drop' value='".'Drop'."'>".confirm(sprintf('Drop %s?',$ea))."\n";}else{echo'Name'.": <input name='name' value='".h($K['name'])."' autocapitalize='off'>\n",doc_link(array('pgsql'=>"datatype-enum.html",),"?");textarea("as",$K["as"]);echo"<p><input type='submit' value='".'Save'."'>\n";}echo
input_token(),'</form>
';}elseif(isset($_GET["check"])){$a=$_GET["check"];$B=$_GET["name"];$K=$_POST;if($K&&!$l){if(JUSH=="sqlite")$I=recreate_table($a,$a,array(),array(),array(),"",array(),"$B",($K["drop"]?"":$K["clause"]));else{$I=($B==""||queries("ALTER TABLE ".table($a)." DROP CONSTRAINT ".idf_escape($B)));if(!$K["drop"])$I=queries("ALTER TABLE ".table($a)." ADD".($K["name"]!=""?" CONSTRAINT ".idf_escape($K["name"]):"")." CHECK ($K[clause])");}queries_redirect(ME."table=".urlencode($a),($K["drop"]?'Check has been dropped.':($B!=""?'Check has been altered.':'Check has been created.')),$I);}page_header(($B!=""?'Alter check'.": ".h($B):'Create check'),$l,array("table"=>$a));if(!$K){$ab=driver()->checkConstraints($a);$K=array("name"=>$B,"clause"=>$ab[$B]);}echo'
<form action="" method="post">
<p>';if(JUSH!="sqlite")echo'Name'.': <input name="name" value="'.h($K["name"]).'" data-maxlength="64" autocapitalize="off"> ';echo
doc_link(array('sql'=>"create-table-check-constraints.html",'mariadb'=>"constraint/",'pgsql'=>"ddl-constraints.html#DDL-CONSTRAINTS-CHECK-CONSTRAINTS",'mssql'=>"relational-databases/tables/create-check-constraints",'sqlite'=>"lang_createtable.html#check_constraints",),"?"),'<p>';textarea("clause",$K["clause"]);echo'<p><input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["trigger"])){$a=$_GET["trigger"];$B="$_GET[name]";$jj=trigger_options();$K=(array)trigger($B,$a)+array("Trigger"=>$a."_bi");if($_POST){if(!$l&&in_array($_POST["Timing"],$jj["Timing"])&&in_array($_POST["Event"],$jj["Event"])&&in_array($_POST["Type"],$jj["Type"])){$Sf=" ON ".table($a);$ic="DROP TRIGGER ".idf_escape($B).(JUSH=="pgsql"?$Sf:"");$Se=ME."table=".urlencode($a);if($_POST["drop"])query_redirect($ic,$Se,'Trigger has been dropped.');else{if($B!="")queries($ic);queries_redirect($Se,($B!=""?'Trigger has been altered.':'Trigger has been created.'),queries(create_trigger($Sf,$_POST)));if($B!="")queries(create_trigger($Sf,$K+array("Type"=>reset($jj["Type"]))));}}$K=$_POST;}page_header(($B!=""?'Alter trigger'.": ".h($B):'Create trigger'),$l,array("table"=>$a));echo'
<form action="" method="post" id="form">
<table class="layout">
<tr><th>Time<td>',html_select("Timing",$jj["Timing"],$K["Timing"],"triggerChange(/^".preg_quote($a,"/")."_[ba][iud]$/, '".js_escape($a)."', this.form);"),'<tr><th>Event<td>',html_select("Event",$jj["Event"],$K["Event"],"this.form['Timing'].onchange();"),(in_array("UPDATE OF",$jj["Event"])?" <input name='Of' value='".h($K["Of"])."' class='hidden'>":""),'<tr><th>Type<td>',html_select("Type",$jj["Type"],$K["Type"]),'</table>
<p>Name: <input name="Trigger" value="',h($K["Trigger"]),'" data-maxlength="64" autocapitalize="off">
',script("qs('#form')['Timing'].onchange();"),'<p>';textarea("Statement",$K["Statement"]);echo'<p>
<input type="submit" value="Save">
';if($B!="")echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',$B));echo
input_token(),'</form>
';}elseif(isset($_GET["user"])){$fa=$_GET["user"];$Zg=array(""=>array("All privileges"=>""));foreach(get_rows("SHOW PRIVILEGES")as$K){foreach(explode(",",($K["Privilege"]=="Grant option"?"":$K["Context"]))as$_b)$Zg[$_b][$K["Privilege"]]=$K["Comment"];}$Zg["Server Admin"]+=$Zg["File access on server"];$Zg["Databases"]["Create routine"]=$Zg["Procedures"]["Create routine"];unset($Zg["Procedures"]["Create routine"]);$Zg["Columns"]=array();foreach(array("Select","Insert","Update","References")as$X)$Zg["Columns"][$X]=$Zg["Tables"][$X];unset($Zg["Server Admin"]["Usage"]);foreach($Zg["Tables"]as$x=>$X)unset($Zg["Databases"][$x]);$Af=array();if($_POST){foreach($_POST["objects"]as$x=>$X)$Af[$X]=(array)$Af[$X]+idx($_POST["grants"],$x,array());}$vd=array();$Qf="";if(isset($_GET["host"])&&($I=connection()->query("SHOW GRANTS FOR ".q($fa)."@".q($_GET["host"])))){while($K=$I->fetch_row()){if(preg_match('~GRANT (.*) ON (.*) TO ~',$K[0],$A)&&preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~',$A[1],$Ze,PREG_SET_ORDER)){foreach($Ze
as$X){if($X[1]!="USAGE")$vd["$A[2]$X[2]"][$X[1]]=true;if(preg_match('~ WITH GRANT OPTION~',$K[0]))$vd["$A[2]$X[2]"]["GRANT OPTION"]=true;}}if(preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~",$K[0],$A))$Qf=$A[1];}}if($_POST&&!$l){$Rf=(isset($_GET["host"])?q($fa)."@".q($_GET["host"]):"''");if($_POST["drop"])query_redirect("DROP USER $Rf",ME."privileges=",'User has been dropped.');else{$Cf=q($_POST["user"])."@".q($_POST["host"]);$Eg=$_POST["pass"];if($Eg!=''&&!$_POST["hashed"]&&!min_version(8)){$Eg=get_val("SELECT PASSWORD(".q($Eg).")");$l=!$Eg;}$Eb=false;if(!$l){if($Rf!=$Cf){$Eb=queries((min_version(5)?"CREATE USER":"GRANT USAGE ON *.* TO")." $Cf IDENTIFIED BY ".(min_version(8)?"":"PASSWORD ").q($Eg));$l=!$Eb;}elseif($Eg!=$Qf)queries("SET PASSWORD FOR $Cf = ".q($Eg));}if(!$l){$xh=array();foreach($Af
as$Kf=>$ud){if(isset($_GET["grant"]))$ud=array_filter($ud);$ud=array_keys($ud);if(isset($_GET["grant"]))$xh=array_diff(array_keys(array_filter($Af[$Kf],'strlen')),$ud);elseif($Rf==$Cf){$Of=array_keys((array)$vd[$Kf]);$xh=array_diff($Of,$ud);$ud=array_diff($ud,$Of);unset($vd[$Kf]);}if(preg_match('~^(.+)\s*(\(.*\))?$~U',$Kf,$A)&&(!grant("REVOKE",$xh,$A[2]," ON $A[1] FROM $Cf")||!grant("GRANT",$ud,$A[2]," ON $A[1] TO $Cf"))){$l=true;break;}}}if(!$l&&isset($_GET["host"])){if($Rf!=$Cf)queries("DROP USER $Rf");elseif(!isset($_GET["grant"])){foreach($vd
as$Kf=>$xh){if(preg_match('~^(.+)(\(.*\))?$~U',$Kf,$A))grant("REVOKE",array_keys($xh),$A[2]," ON $A[1] FROM $Cf");}}}queries_redirect(ME."privileges=",(isset($_GET["host"])?'User has been altered.':'User has been created.'),!$l);if($Eb)connection()->query("DROP USER $Cf");}}page_header((isset($_GET["host"])?'Username'.": ".h("$fa@$_GET[host]"):'Create user'),$l,array("privileges"=>array('','Privileges')));$K=$_POST;if($K)$vd=$Af;else{$K=$_GET+array("host"=>get_val("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)"));$K["pass"]=$Qf;if($Qf!="")$K["hashed"]=true;$vd[(DB==""||$vd?"":idf_escape(addcslashes(DB,"%_\\"))).".*"]=array();}echo'<form action="" method="post">
<table class="layout">
<tr><th>Server<td><input name="host" data-maxlength="60" value="',h($K["host"]),'" autocapitalize="off">
<tr><th>Username<td><input name="user" data-maxlength="80" value="',h($K["user"]),'" autocapitalize="off">
<tr><th>Password<td><input name="pass" id="pass" value="',h($K["pass"]),'" autocomplete="new-password">
',($K["hashed"]?"":script("typePassword(qs('#pass'));")),(min_version(8)?"":checkbox("hashed",1,$K["hashed"],'Hashed',"typePassword(this.form['pass'], this.checked);")),'</table>

',"<table class='odds'>\n","<thead><tr><th colspan='2'>".'Privileges'.doc_link(array('sql'=>"grant.html#priv_level"));$s=0;foreach($vd
as$Kf=>$ud){echo'<th>'.($Kf!="*.*"?"<input name='objects[$s]' value='".h($Kf)."' size='10' autocapitalize='off'>":input_hidden("objects[$s]","*.*")."*.*");$s++;}echo"</thead>\n";foreach(array(""=>"","Server Admin"=>'Server',"Databases"=>'Database',"Tables"=>'Table',"Columns"=>'Column',"Procedures"=>'Routine',)as$_b=>$Yb){foreach((array)$Zg[$_b]as$Yg=>$ob){echo"<tr><td".($Yb?">$Yb<td":" colspan='2'").' lang="en" title="'.h($ob).'">'.h($Yg);$s=0;foreach($vd
as$Kf=>$ud){$B="'grants[$s][".h(strtoupper($Yg))."]'";$Y=$ud[strtoupper($Yg)];if($_b=="Server Admin"&&$Kf!=(isset($vd["*.*"])?"*.*":".*"))echo"<td>";elseif(isset($_GET["grant"]))echo"<td><select name=$B><option><option value='1'".($Y?" selected":"").">".'Grant'."<option value='0'".($Y=="0"?" selected":"").">".'Revoke'."</select>";else
echo"<td align='center'><label class='block'>","<input type='checkbox' name=$B value='1'".($Y?" checked":"").($Yg=="All privileges"?" id='grants-$s-all'>":">".($Yg=="Grant option"?"":script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$s-all'); };"))),"</label>";$s++;}}}echo"</table>\n",'<p>
<input type="submit" value="Save">
';if(isset($_GET["host"]))echo'<input type="submit" name="drop" value="Drop">',confirm(sprintf('Drop %s?',"$fa@$_GET[host]"));echo
input_token(),'</form>
';}elseif(isset($_GET["processlist"])){if(support("kill")){if($_POST&&!$l){$Ee=0;foreach((array)$_POST["kill"]as$X){if(adminer()->killProcess($X))$Ee++;}queries_redirect(ME."processlist=",lang_format(array('%d process has been killed.','%d processes have been killed.'),$Ee),$Ee||!$_POST["kill"]);}}page_header('Process list',$l);echo'
<form action="" method="post">
<div class="scrollable">
<table class="nowrap checkable odds">
',script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");$s=-1;foreach(adminer()->processList()as$s=>$K){if(!$s){echo"<thead><tr lang='en'>".(support("kill")?"<th>":"");foreach($K
as$x=>$X)echo"<th>$x".doc_link(array('sql'=>"show-processlist.html#processlist_".strtolower($x),'pgsql'=>"monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",'oracle'=>"REFRN30223",));echo"</thead>\n";}echo"<tr>".(support("kill")?"<td>".checkbox("kill[]",$K[JUSH=="sql"?"Id":"pid"],0):"");foreach($K
as$x=>$X)echo"<td>".((JUSH=="sql"&&$x=="Info"&&preg_match("~Query|Killed~",$K["Command"])&&$X!="")||(JUSH=="pgsql"&&$x=="current_query"&&$X!="<IDLE>")||(JUSH=="oracle"&&$x=="sql_text"&&$X!="")?"<code class='jush-".JUSH."'>".shorten_utf8($X,100,"</code>").' <a href="'.h(ME.($K["db"]!=""?"db=".urlencode($K["db"])."&":"")."sql=".urlencode($X)).'">'.'Clone'.'</a>':h($X));echo"\n";}echo'</table>
</div>
<p>
';if(support("kill"))echo($s+1)."/".sprintf('%d in total',max_connections()),"<p><input type='submit' value='".'Kill'."'>\n";echo
input_token(),'</form>
',script("tableCheck();");}elseif(isset($_GET["select"])){$a=$_GET["select"];$S=table_status1($a);$w=indexes($a);$n=fields($a);$ld=column_foreign_keys($a);$Mf=$S["Oid"];$na=get_settings("adminer_import");$yh=array();$e=array();$Lh=array();$eg=array();$Oi="";foreach($n
as$x=>$m){$B=adminer()->fieldName($m);$zf=html_entity_decode(strip_tags($B),ENT_QUOTES);if(isset($m["privileges"]["select"])&&$B!=""){$e[$x]=$zf;if(is_shortable($m))$Oi=adminer()->selectLengthProcess();}if(isset($m["privileges"]["where"])&&$B!="")$Lh[$x]=$zf;if(isset($m["privileges"]["order"])&&$B!="")$eg[$x]=$zf;$yh+=$m["privileges"];}list($M,$wd)=adminer()->selectColumnsProcess($e,$w);$M=array_unique($M);$wd=array_unique($wd);$ue=count($wd)<count($M);$Z=adminer()->selectSearchProcess($n,$w);$dg=adminer()->selectOrderProcess($n,$w);$z=adminer()->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$sj=>$K){$wa=convert_field($n[key($K)]);$M=array($wa?:idf_escape(key($K)));$Z[]=where_check($sj,$n);$J=driver()->select($a,$M,$Z,$M);if($J)echo
first($J->fetch_row());}exit;}$G=$uj=array();foreach($w
as$v){if($v["type"]=="PRIMARY"){$G=array_flip($v["columns"]);$uj=($M?$G:array());foreach($uj
as$x=>$X){if(in_array(idf_escape($x),$M))unset($uj[$x]);}break;}}if($Mf&&!$G){$G=$uj=array($Mf=>0);$w[]=array("type"=>"PRIMARY","columns"=>array($Mf));}if($_POST&&!$l){$Tj=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$ab=array();foreach($_POST["check"]as$Wa)$ab[]=where_check($Wa,$n);$Tj[]="((".implode(") OR (",$ab)."))";}$Tj=($Tj?"\nWHERE ".implode(" AND ",$Tj):"");if($_POST["export"]){save_settings(array("output"=>$_POST["output"],"format"=>$_POST["format"]),"adminer_import");dump_headers($a);adminer()->dumpTable($a,"");$pd=($M?implode(", ",$M):"*").convert_fields($e,$n,$M)."\nFROM ".table($a);$yd=($wd&&$ue?"\nGROUP BY ".implode(", ",$wd):"").($dg?"\nORDER BY ".implode(", ",$dg):"");$H="SELECT $pd$Tj$yd";if(is_array($_POST["check"])&&!$G){$qj=array();foreach($_POST["check"]as$X)$qj[]="(SELECT".limit($pd,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n).$yd,1).")";$H=implode(" UNION ALL ",$qj);}adminer()->dumpData($a,"table",$H);adminer()->dumpFooter();exit;}if(!adminer()->selectEmailProcess($Z,$ld)){if($_POST["save"]||$_POST["delete"]){$I=true;$oa=0;$O=array();if(!$_POST["delete"]){foreach($_POST["fields"]as$B=>$X){$X=process_input($n[$B]);if($X!==null&&($_POST["clone"]||$X!==false))$O[idf_escape($B)]=($X!==false?$X:idf_escape($B));}}if($_POST["delete"]||$O){$H=($_POST["clone"]?"INTO ".table($a)." (".implode(", ",array_keys($O)).")\nSELECT ".implode(", ",$O)."\nFROM ".table($a):"");if($_POST["all"]||($G&&is_array($_POST["check"]))||$ue){$I=($_POST["delete"]?driver()->delete($a,$Tj):($_POST["clone"]?queries("INSERT $H$Tj".driver()->insertReturning($a)):driver()->update($a,$O,$Tj)));$oa=connection()->affected_rows;if(is_object($I))$oa+=$I->num_rows;}else{foreach((array)$_POST["check"]as$X){$Sj="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($X,$n);$I=($_POST["delete"]?driver()->delete($a,$Sj,1):($_POST["clone"]?queries("INSERT".limit1($a,$H,$Sj)):driver()->update($a,$O,$Sj,1)));if(!$I)break;$oa+=connection()->affected_rows;}}}$lf=lang_format(array('%d item has been affected.','%d items have been affected.'),$oa);if($_POST["clone"]&&$I&&$oa==1){$Je=last_id($I);if($Je)$lf=sprintf('Item%s has been inserted.'," $Je");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$lf,$I);if(!$_POST["delete"]){$Qg=(array)$_POST["fields"];edit_form($a,array_intersect_key($n,$Qg),$Qg,!$_POST["clone"],$l);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$l='Ctrl+click on a value to modify it.';else{$I=true;$oa=0;foreach($_POST["val"]as$sj=>$K){$O=array();foreach($K
as$x=>$X){$x=bracket_escape($x,true);$O[idf_escape($x)]=(preg_match('~char|text~',$n[$x]["type"])||$X!=""?adminer()->processInput($n[$x],$X):"NULL");}$I=driver()->update($a,$O," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($sj,$n),($ue||$G?0:1)," ");if(!$I)break;$oa+=connection()->affected_rows;}queries_redirect(remove_from_uri(),lang_format(array('%d item has been affected.','%d items have been affected.'),$oa),$I);}}elseif(!is_string($Zc=get_file("csv_file",true)))$l=upload_error($Zc);elseif(!preg_match('~~u',$Zc))$l='File must be in UTF-8 encoding.';else{save_settings(array("output"=>$na["output"],"format"=>$_POST["separator"]),"adminer_import");$I=true;$kb=array_keys($n);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Zc,$Ze);$oa=count($Ze[0]);driver()->begin();$Rh=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$L=array();foreach($Ze[0]as$x=>$X){preg_match_all("~((?>\"[^\"]*\")+|[^$Rh]*)$Rh~",$X.$Rh,$af);if(!$x&&!array_diff($af[1],$kb)){$kb=$af[1];$oa--;}else{$O=array();foreach($af[1]as$s=>$hb)$O[idf_escape($kb[$s])]=($hb==""&&$n[$kb[$s]]["null"]?"NULL":q(preg_match('~^".*"$~s',$hb)?str_replace('""','"',substr($hb,1,-1)):$hb));$L[]=$O;}}$I=(!$L||driver()->insertUpdate($a,$L,$G));if($I)driver()->commit();queries_redirect(remove_from_uri("page"),lang_format(array('%d row has been imported.','%d rows have been imported.'),$oa),$I);driver()->rollback();}}}$_i=adminer()->tableName($S);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $_i",$l);$O=null;if(isset($yh["insert"])||!support("table")){$vg=array();foreach((array)$_GET["where"]as$X){if(isset($ld[$X["col"]])&&count($ld[$X["col"]])==1&&($X["op"]=="="||(!$X["op"]&&(is_array($X["val"])||!preg_match('~[_%]~',$X["val"])))))$vg["set"."[".bracket_escape($X["col"])."]"]=$X["val"];}$O=$vg?"&".http_build_query($vg):"";}adminer()->selectLinks($S,$O);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($n?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?input_hidden("db",DB).(isset($_GET["ns"])?input_hidden("ns",$_GET["ns"]):""):""),input_hidden("select",$a),"</div>\n";adminer()->selectColumnsPrint($M,$e);adminer()->selectSearchPrint($Z,$Lh,$w);adminer()->selectOrderPrint($dg,$eg,$w);adminer()->selectLimitPrint($z);adminer()->selectLengthPrint($Oi);adminer()->selectActionPrint($w);echo"</form>\n";$D=$_GET["page"];$od=null;if($D=="last"){$od=get_val(count_rows($a,$Z,$ue,$wd));$D=floor(max(0,intval($od)-1)/$z);}$Mh=$M;$xd=$wd;if(!$Mh){$Mh[]="*";$Ab=convert_fields($e,$n,$M);if($Ab)$Mh[]=substr($Ab,2);}foreach($M
as$x=>$X){$m=$n[idf_unescape($X)];if($m&&($wa=convert_field($m)))$Mh[$x]="$wa AS $X";}if(!$ue&&$uj){foreach($uj
as$x=>$X){$Mh[]=idf_escape($x);if($xd)$xd[]=idf_escape($x);}}$I=driver()->select($a,$Mh,$Z,$xd,$dg,$z,$D,true);if(!$I)echo"<p class='error'>".error()."\n";else{if(JUSH=="mssql"&&$D)$I->seek($z*$D);$vc=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$L=array();while($K=$I->fetch_assoc()){if($D&&JUSH=="oracle")unset($K["RNUM"]);$L[]=$K;}if($_GET["page"]!="last"&&$z&&$wd&&$ue&&JUSH=="sql")$od=get_val(" SELECT FOUND_ROWS()");if(!$L)echo"<p class='message'>".'No rows.'."\n";else{$Ea=adminer()->backwardKeys($a,$_i);echo"<div class='scrollable'>","<table id='table' class='nowrap checkable odds'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$wd&&$M?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$_f=array();$rd=array();reset($M);$ih=1;foreach($L[0]as$x=>$X){if(!isset($uj[$x])){$X=idx($_GET["columns"],key($M))?:array();$m=$n[$M?($X?$X["col"]:current($M)):$x];$B=($m?adminer()->fieldName($m,$ih):($X["fun"]?"*":h($x)));if($B!=""){$ih++;$_f[$x]=$B;$d=idf_escape($x);$Nd=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$Yb="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});","");$qd=apply_sql_function($X["fun"],$B);$ei=isset($m["privileges"]["order"])||$qd;echo($ei?"<a href='".h($Nd.($dg[0]==$d||$dg[0]==$x?$Yb:''))."'>$qd</a>":$qd),"<span class='column hidden'>";if($ei)echo"<a href='".h($Nd.$Yb)."' title='".'descending'."' class='text'> ‚Üì</a>";if(!$X["fun"]&&isset($m["privileges"]["where"]))echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");echo"</span>";}$rd[$x]=$X["fun"];next($M);}}$Oe=array();if($_GET["modify"]){foreach($L
as$K){foreach($K
as$x=>$X)$Oe[$x]=max($Oe[$x],min(40,strlen(utf8_decode($X))));}}echo($Ea?"<th>".'Relations':"")."</thead>\n";if(is_ajax())ob_end_clean();foreach(adminer()->rowDescriptions($L,$ld)as$yf=>$K){$rj=unique_array($L[$yf],$w);if(!$rj){$rj=array();reset($M);foreach($L[$yf]as$x=>$X){if(!preg_match('~^(COUNT|AVG|GROUP_CONCAT|MAX|MIN|SUM)\(~',current($M)))$rj[$x]=$X;next($M);}}$sj="";foreach($rj
as$x=>$X){$m=(array)$n[$x];if((JUSH=="sql"||JUSH=="pgsql")&&preg_match('~char|text|enum|set~',$m["type"])&&strlen($X)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".(JUSH!='sql'||preg_match("~^utf8~",$m["collation"])?$x:"CONVERT($x USING ".charset(connection()).")").")";$X=md5($X);}$sj
.="&".($X!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($X===false?"f":$X):"null%5B%5D=".urlencode($x));}echo"<tr>".(!$wd&&$M?"":"<td>".checkbox("check[]",substr($sj,1),in_array(substr($sj,1),(array)$_POST["check"])).($ue||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$sj)."' class='edit'>".'edit'."</a>"));reset($M);foreach($K
as$x=>$X){if(isset($_f[$x])){$d=current($M);$m=(array)$n[$x];$X=driver()->value($X,$m);if($X!=""&&(!isset($vc[$x])||$vc[$x]!=""))$vc[$x]=(is_mail($X)?$_f[$x]:"");$_="";if(is_blob($m)&&$X!="")$_=ME.'download='.urlencode($a).'&field='.urlencode($x).$sj;if(!$_&&$X!==null){foreach((array)$ld[$x]as$p){if(count($ld[$x])==1||end($p["source"])==$x){$_="";foreach($p["source"]as$s=>$fi)$_
.=where_link($s,$p["target"][$s],$L[$yf][$fi]);$_=($p["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($p["db"]),ME):ME).'select='.urlencode($p["table"]).$_;if($p["ns"])$_=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($p["ns"]),$_);if(count($p["source"])==1)break;}}}if($d=="COUNT(*)"){$_=ME."select=".urlencode($a);$s=0;foreach((array)$_GET["where"]as$W){if(!array_key_exists($W["col"],$rj))$_
.=where_link($s++,$W["col"],$W["val"],$W["op"]);}foreach($rj
as$Ae=>$W)$_
.=where_link($s++,$Ae,$W);}$Od=select_value($X,$_,$m,$Oi);$t=h("val[$sj][".bracket_escape($x)."]");$Rg=idx(idx($_POST["val"],$sj),bracket_escape($x));$qc=!is_array($K[$x])&&is_utf8($Od)&&$L[$yf][$x]==$K[$x]&&!$rd[$x]&&!$m["generated"];$U=(preg_match('~^(AVG|MIN|MAX)\((.+)\)~',$d,$A)?$n[idf_unescape($A[2])]["type"]:$m["type"]);$Mi=preg_match('~text|json|lob~',$U);$ve=preg_match(number_type(),$U)||preg_match('~^(CHAR_LENGTH|ROUND|FLOOR|CEIL|TIME_TO_SEC|COUNT|SUM)\(~',$d);echo"<td id='$t'".($ve&&($X===null||is_numeric(strip_tags($Od))||$U=="money")?" class='number'":"");if(($_GET["modify"]&&$qc&&$X!==null)||$Rg!==null){$Ad=h($Rg!==null?$Rg:$K[$x]);echo">".($Mi?"<textarea name='$t' cols='30' rows='".(substr_count($K[$x],"\n")+1)."'>$Ad</textarea>":"<input name='$t' value='$Ad' size='$Oe[$x]'>");}else{$Ue=strpos($Od,"<i>‚Ä¶</i>");echo" data-text='".($Ue?2:($Mi?1:0))."'".($qc?"":" data-warning='".h('Use edit link to modify this value.')."'").">$Od";}}next($M);}if($Ea)echo"<td>";adminer()->backwardKeysPrint($Ea,$L[$yf]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($L||$D){$Ic=true;if($_GET["page"]!="last"){if(!$z||(count($L)<$z&&($L||!$D)))$od=($D?$D*$z:0)+count($L);elseif(JUSH!="sql"||!$ue){$od=($ue?false:found_rows($S,$Z));if(intval($od)<max(1e4,2*($D+1)*$z))$od=first(slow_query(count_rows($a,$Z,$ue,$wd)));else$Ic=false;}}$tg=($z&&($od===false||$od>$z||$D));if($tg)echo(($od===false?count($L)+1:$od-$D*$z)>$z?'<p><a href="'.h(remove_from_uri("page")."&page=".($D+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, $z, '".'Loading'."‚Ä¶');",""):''),"\n";echo"<div class='footer'><div>\n";if($tg){$ef=($od===false?$D+(count($L)>=$z?2:1):floor(($od-1)/$z));echo"<fieldset>";if(JUSH!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($D+1)."')); return false; };"),pagination(0,$D).($D>5?" ‚Ä¶":"");for($s=max(1,$D-4);$s<min($ef,$D+5);$s++)echo
pagination($s,$D);if($ef>0)echo($D+5<$ef?" ‚Ä¶":""),($Ic&&$od!==false?pagination($ef,$D):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$ef'>".'last'."</a>");}else
echo"<legend>".'Page'."</legend>",pagination(0,$D).($D>1?" ‚Ä¶":""),($D?pagination($D,$D):""),($ef>$D?pagination($D+1,$D).($ef>$D+1?" ‚Ä¶":""):"");echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$fc=($Ic?"":"~ ").$od;$Wf="const checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$fc' : checked); selectCount('selected2', this.checked || !checked ? '$fc' : checked);";echo
checkbox("all",1,0,($od!==false?($Ic?"":"~ ").lang_format(array('%d row','%d rows'),$od):""),$Wf)."\n","</fieldset>\n";if(adminer()->selectCommandPrint())echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';$md=adminer()->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($md['sql']);break;}}if($md){print_fieldset("export",'Export'." <span id='selected2'></span>");$qg=adminer()->dumpOutput();echo($qg?html_select("output",$qg,$na["output"])." ":""),html_select("format",$md,$na["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}adminer()->selectEmailPrint(array_filter($vc,'strlen'),$e);echo"</div></div>\n";}if(adminer()->selectImportPrint())echo"<p>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import'".($_POST["import"]?"":" class='hidden'").">: ",file_input("<input type='file' name='csv_file'> ".html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$na["format"])." <input type='submit' name='import' value='".'Import'."'>"),"</span>";echo
input_token(),"</form>\n",(!$wd&&$M?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["variables"])){$P=isset($_GET["status"]);page_header($P?'Status':'Variables');$Jj=($P?show_status():show_variables());if(!$Jj)echo"<p class='message'>".'No rows.'."\n";else{echo"<table>\n";foreach($Jj
as$K){echo"<tr>";$x=array_shift($K);echo"<th><code class='jush-".JUSH.($P?"status":"set")."'>".h($x)."</code>";foreach($K
as$X)echo"<td>".nl_br(h($X));}echo"</table>\n";}}elseif(isset($_GET["script"])){header("Content-Type: text/javascript; charset=utf-8");if($_GET["script"]=="db"){$wi=array("Data_length"=>0,"Index_length"=>0,"Data_free"=>0);foreach(table_status()as$B=>$S){json_row("Comment-$B",h($S["Comment"]));if(!is_view($S)||preg_match('~materialized~i',$S["Engine"])){foreach(array("Engine","Collation")as$x)json_row("$x-$B",h($S[$x]));foreach($wi+array("Auto_increment"=>0,"Rows"=>0)as$x=>$X){if($S[$x]!=""){$X=format_number($S[$x]);if($X>=0)json_row("$x-$B",($x=="Rows"&&$X&&$S["Engine"]==(JUSH=="pgsql"?"table":"InnoDB")?"~ $X":$X));if(isset($wi[$x]))$wi[$x]+=($S["Engine"]!="InnoDB"||$x!="Data_free"?$S[$x]:0);}elseif(array_key_exists($x,$S))json_row("$x-$B","?");}}}foreach($wi
as$x=>$X)json_row("sum-$x",format_number($X));json_row("");}elseif($_GET["script"]=="kill")connection()->query("KILL ".number($_POST["kill"]));else{foreach(count_tables(adminer()->databases())as$j=>$X){json_row("tables-$j",$X);json_row("size-$j",db_size($j));}json_row("");}exit;}else{$Gi=array_merge((array)$_POST["tables"],(array)$_POST["views"]);if($Gi&&!$l&&!$_POST["search"]){$I=true;$lf="";if(JUSH=="sql"&&$_POST["tables"]&&count($_POST["tables"])>1&&($_POST["drop"]||$_POST["truncate"]||$_POST["copy"]))queries("SET foreign_key_checks = 0");if($_POST["truncate"]){if($_POST["tables"])$I=truncate_tables($_POST["tables"]);$lf='Tables have been truncated.';}elseif($_POST["move"]){$I=move_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been moved.';}elseif($_POST["copy"]){$I=copy_tables((array)$_POST["tables"],(array)$_POST["views"],$_POST["target"]);$lf='Tables have been copied.';}elseif($_POST["drop"]){if($_POST["views"])$I=drop_views($_POST["views"]);if($I&&$_POST["tables"])$I=drop_tables($_POST["tables"]);$lf='Tables have been dropped.';}elseif(JUSH=="sqlite"&&$_POST["check"]){foreach((array)$_POST["tables"]as$R){foreach(get_rows("PRAGMA integrity_check(".q($R).")")as$K)$lf
.="<b>".h($R)."</b>: ".h($K["integrity_check"])."<br>";}}elseif(JUSH!="sql"){$I=(JUSH=="sqlite"?queries("VACUUM"):apply_queries("VACUUM".($_POST["optimize"]?"":" ANALYZE"),$_POST["tables"]));$lf='Tables have been optimized.';}elseif(!$_POST["tables"])$lf='No tables.';elseif($I=queries(($_POST["optimize"]?"OPTIMIZE":($_POST["check"]?"CHECK":($_POST["repair"]?"REPAIR":"ANALYZE")))." TABLE ".implode(", ",array_map('Adminer\idf_escape',$_POST["tables"])))){while($K=$I->fetch_assoc())$lf
.="<b>".h($K["Table"])."</b>: ".h($K["Msg_text"])."<br>";}queries_redirect(substr(ME,0,-1),$lf,$I);}page_header(($_GET["ns"]==""?'Database'.": ".h(DB):'Schema'.": ".h($_GET["ns"])),$l,true);if(adminer()->homepage()){if($_GET["ns"]!==""){echo"<h3 id='tables-views'>".'Tables and views'."</h3>\n";$Fi=tables_list();if(!$Fi)echo"<p class='message'>".'No tables.'."\n";else{echo"<form action='' method='post'>\n";if(support("table")){echo"<fieldset><legend>".'Search data in tables'." <span id='selected2'></span></legend><div>",html_select("op",adminer()->operators(),idx($_POST,"op",JUSH=="elastic"?"should":"LIKE %%"))," <input type='search' name='query' value='".h($_POST["query"])."'>",script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');","")," <input type='submit' name='search' value='".'Search'."'>\n","</div></fieldset>\n";if($_POST["search"]&&$_POST["query"]!=""){$_GET["where"][0]["op"]=$_POST["op"];search_tables();}}echo"<div class='scrollable'>\n","<table class='nowrap checkable odds'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);",""),'<th>'.'Table','<td>'.'Engine'.doc_link(array('sql'=>'storage-engines.html')),'<td>'.'Collation'.doc_link(array('sql'=>'charset-charsets.html','mariadb'=>'supported-character-sets-and-collations/')),'<td>'.'Data Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT','oracle'=>'REFRN20286')),'<td>'.'Index Length'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-admin.html#FUNCTIONS-ADMIN-DBOBJECT')),'<td>'.'Data Free'.doc_link(array('sql'=>'show-table-status.html')),'<td>'.'Auto Increment'.doc_link(array('sql'=>'example-auto-increment.html','mariadb'=>'auto_increment/')),'<td>'.'Rows'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'catalog-pg-class.html#CATALOG-PG-CLASS','oracle'=>'REFRN20286')),(support("comment")?'<td>'.'Comment'.doc_link(array('sql'=>'show-table-status.html','pgsql'=>'functions-info.html#FUNCTIONS-INFO-COMMENT-TABLE')):''),"</thead>\n";$T=0;foreach($Fi
as$B=>$U){$Mj=($U!==null&&!preg_match('~table|sequence~i',$U));$t=h("Table-".$B);echo'<tr><td>'.checkbox(($Mj?"views[]":"tables[]"),$B,in_array("$B",$Gi,true),"","","",$t),'<th>'.(support("table")||support("indexes")?"<a href='".h(ME)."table=".urlencode($B)."' title='".'Show structure'."' id='$t'>".h($B).'</a>':h($B));if($Mj&&!preg_match('~materialized~i',$U)){$Si='View';echo'<td colspan="6">'.(support("view")?"<a href='".h(ME)."view=".urlencode($B)."' title='".'Alter view'."'>$Si</a>":$Si),'<td align="right"><a href="'.h(ME)."select=".urlencode($B).'" title="'.'Select data'.'">?</a>';}else{foreach(array("Engine"=>array(),"Collation"=>array(),"Data_length"=>array("create",'Alter table'),"Index_length"=>array("indexes",'Alter indexes'),"Data_free"=>array("edit",'New item'),"Auto_increment"=>array("auto_increment=1&create",'Alter table'),"Rows"=>array("select",'Select data'),)as$x=>$_){$t=" id='$x-".h($B)."'";echo($_?"<td align='right'>".(support("table")||$x=="Rows"||(support("indexes")&&$x!="Data_length")?"<a href='".h(ME."$_[0]=").urlencode($B)."'$t title='$_[1]'>?</a>":"<span$t>?</span>"):"<td id='$x-".h($B)."'>");}$T++;}echo(support("comment")?"<td id='Comment-".h($B)."'>":""),"\n";}echo"<tr><td><th>".sprintf('%d in total',count($Fi)),"<td>".h(JUSH=="sql"?get_val("SELECT @@default_storage_engine"):""),"<td>".h(db_collation(DB,collations()));foreach(array("Data_length","Index_length","Data_free")as$x)echo"<td align='right' id='sum-$x'>";echo"\n","</table>\n",script("ajaxSetHtml('".js_escape(ME)."script=db');"),"</div>\n";if(!information_schema(DB)){echo"<div class='footer'><div>\n";$Gj="<input type='submit' value='".'Vacuum'."'> ".on_help("'VACUUM'");$Zf="<input type='submit' name='optimize' value='".'Optimize'."'> ".on_help(JUSH=="sql"?"'OPTIMIZE TABLE'":"'VACUUM OPTIMIZE'");echo"<fieldset><legend>".'Selected'." <span id='selected'></span></legend><div>".(JUSH=="sqlite"?$Gj."<input type='submit' name='check' value='".'Check'."'> ".on_help("'PRAGMA integrity_check'"):(JUSH=="pgsql"?$Gj.$Zf:(JUSH=="sql"?"<input type='submit' value='".'Analyze'."'> ".on_help("'ANALYZE TABLE'").$Zf."<input type='submit' name='check' value='".'Check'."'> ".on_help("'CHECK TABLE'")."<input type='submit' name='repair' value='".'Repair'."'> ".on_help("'REPAIR TABLE'"):"")))."<input type='submit' name='truncate' value='".'Truncate'."'> ".on_help(JUSH=="sqlite"?"'DELETE'":"'TRUNCATE".(JUSH=="pgsql"?"'":" TABLE'")).confirm()."<input type='submit' name='drop' value='".'Drop'."'>".on_help("'DROP TABLE'").confirm()."\n";$i=(support("scheme")?adminer()->schemas():adminer()->databases());echo"</div></fieldset>\n";$Jh="";if(count($i)!=1&&JUSH!="sqlite"){echo"<fieldset><legend>".'Move to other database'." <span id='selected3'></span></legend><div>";$j=(isset($_POST["target"])?$_POST["target"]:(support("scheme")?$_GET["ns"]:DB));echo($i?html_select("target",$i,$j):'<input name="target" value="'.h($j).'" autocapitalize="off">'),"</label> <input type='submit' name='move' value='".'Move'."'>",(support("copy")?" <input type='submit' name='copy' value='".'Copy'."'> ".checkbox("overwrite",1,$_POST["overwrite"],'overwrite'):""),"</div></fieldset>\n";$Jh=" selectCount('selected3', formChecked(this, /^(tables|views)\[/));";}echo"<input type='hidden' name='all' value=''>",script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));".(support("table")?" selectCount('selected2', formChecked(this, /^tables\[/) || $T);":"")."$Jh }"),input_token(),"</div></div>\n";}echo"</form>\n",script("tableCheck();");}echo"<p class='links'><a href='".h(ME)."create='>".'Create table'."</a>\n",(support("view")?"<a href='".h(ME)."view='>".'Create view'."</a>\n":"");if(support("routine")){echo"<h3 id='routines'>".'Routines'."</h3>\n";$Bh=routines();if($Bh){echo"<table class='odds'>\n",'<thead><tr><th>'.'Name'.'<td>'.'Type'.'<td>'.'Return type'."<td></thead>\n";foreach($Bh
as$K){$B=($K["SPECIFIC_NAME"]==$K["ROUTINE_NAME"]?"":"&name=".urlencode($K["ROUTINE_NAME"]));echo'<tr>','<th><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'callf=':'call=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.h($K["ROUTINE_NAME"]).'</a>','<td>'.h($K["ROUTINE_TYPE"]),'<td>'.h($K["DTD_IDENTIFIER"]),'<td><a href="'.h(ME.($K["ROUTINE_TYPE"]!="PROCEDURE"?'function=':'procedure=').urlencode($K["SPECIFIC_NAME"]).$B).'">'.'Alter'."</a>";}echo"</table>\n";}echo'<p class="links">'.(support("procedure")?'<a href="'.h(ME).'procedure=">'.'Create procedure'.'</a>':'').'<a href="'.h(ME).'function=">'.'Create function'."</a>\n";}if(support("sequence")){echo"<h3 id='sequences'>".'Sequences'."</h3>\n";$Uh=get_vals("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = current_schema() ORDER BY sequence_name");if($Uh){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Uh
as$X)echo"<tr><th><a href='".h(ME)."sequence=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."sequence='>".'Create sequence'."</a>\n";}if(support("type")){echo"<h3 id='user-types'>".'User types'."</h3>\n";$Ej=types();if($Ej){echo"<table class='odds'>\n","<thead><tr><th>".'Name'."</thead>\n";foreach($Ej
as$X)echo"<tr><th><a href='".h(ME)."type=".urlencode($X)."'>".h($X)."</a>\n";echo"</table>\n";}echo"<p class='links'><a href='".h(ME)."type='>".'Create type'."</a>\n";}if(support("event")){echo"<h3 id='events'>".'Events'."</h3>\n";$L=get_rows("SHOW EVENTS");if($L){echo"<table>\n","<thead><tr><th>".'Name'."<td>".'Schedule'."<td>".'Start'."<td>".'End'."<td></thead>\n";foreach($L
as$K)echo"<tr>","<th>".h($K["Name"]),"<td>".($K["Execute at"]?'At given time'."<td>".$K["Execute at"]:'Every'." ".$K["Interval value"]." ".$K["Interval field"]."<td>$K[Starts]"),"<td>$K[Ends]",'<td><a href="'.h(ME).'event='.urlencode($K["Name"]).'">'.'Alter'.'</a>';echo"</table>\n";$Gc=get_val("SELECT @@event_scheduler");if($Gc&&$Gc!="ON")echo"<p class='error'><code class='jush-sqlset'>event_scheduler</code>: ".h($Gc)."\n";}echo'<p class="links"><a href="'.h(ME).'event=">'.'Create event'."</a>\n";}}}}page_footer();
