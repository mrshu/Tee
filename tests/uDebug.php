<?php
class uDebug{
	private static $printed = false;

	public static function dump($var, $ret = false, $name = NULL,$array = false)
	{
		if(!self::$printed){
			self::bar();
		}

		$out = "";
		
		if(!$array){
			$out = "<table>";
		}

		$out .= "<tr><td class='nm' valign='top'>";
		if(!$name){
			$trace = debug_backtrace();
       			$vLine = file( $trace[0]["file"]);
	    		$fLine = $vLine[ $trace[0]['line'] - 1 ];
	        	preg_match( '/(\w+\:\:)?\$(\w+)(\->\w+)?/', $fLine, $match );
			$out .= $match[0];
		}else{
			$out .= $name;
		}

		$out .= "</td><td>";
		
		if(is_bool($var)){
			$out .= "(<span class='kw'>bool</span>) ";
			$out .= ($var ? 'true' : 'false' );
		}else if($var === NULL){
			$out .= "NULL";
		}else if(is_int($var)){
			$out .= "(<span class='kw'>int</span>) ";
			$out .= $var;
		}else if(is_float($var)){
			$out .= "(<span class='kw'>float</span>) ";
			$out .= $var;
			// float value always with dot zero
			if(strpos((string)$var,".") === false){$out .= ".0";};
		}else if(is_string($var)){
			$out .= "(<span class='kw'>string</span>)(";
			$out .= strlen($var);
			$out .= ") <span class='qt'>\"</span>";
			$out .= str_replace(array("\t","\n"),
					    array('<span class=\'sc\'>\t</span>','<span class=\'sc\'>\n</span>'),
					    htmlspecialchars($var));
			$out .= "<span class='qt'>\"</span>";
		}else if(is_array($var)){
			$out .= "(<span class='kw'>array</span>)(";
			$out .= count($var);
			$out .= ")<br />{<br /><div class='ar'><table>";
			foreach($var as $key=>$val){
				if($key === 0){ $key = "0";}
				$out .= self::dump($val,true,(string)$key,true);
				//TODO: 0 key;
			}
			$out .="</table></div>}";
		}

		$out .= "</td></tr>";
		
		if(!$array){
			$out .= "</table>";
		}
		

		if($ret){
			return $out;
		}else{
			// echo it as base64 encoded and pass to javascript 
			// function which will insert it into #dbg div
			echo "<script>dbg_append('";
			echo base64_encode($out);
			echo "','dbg');</script>";
			return $var;
		}
	}
	public static function bar()
	{
		// print debug bar just once
		self::$printed = true;
		// printing CSS styles
		?>
<style>
		#dbg{width:400px;height:300px;overflow:scroll;font-family:Tahoma, arial, serif;z-index:10000;display:none;border:.5px solid #ddd;white-space:nowrap;background-color:#161616;}
		#dbg table{color:#fff;}
		#dbg-wrapper{position:absolute;left:0;bottom:0;background-color:#F2A73D;z-index:10001;margin:3px;padding:4px}
		.toggle-link{text-decoration:none;color:#303030}
		.kw{font-weight:700;color:#8197bf}
		.sc{color:#00F2FF;font-weight:700;margin:0 1px}
		.nm{font-weight:700;color:#fad07a}
		.ar{margin-left:10px}
		.qt{color:#c0f}
</style>
		<?php
		// now javascript functions needed
		?>
<script>
var base64chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'.split("");
var base64inv={};for(var i=0;i<base64chars.length;i++){base64inv[base64chars[i]]=i;}
function base64_decode(s)
{s=s.replace(new RegExp('[^'+base64chars.join("")+'=]','g'),"");var p=(s.charAt(s.length-1)=='='?(s.charAt(s.length-2)=='='?'AA':'A'):"");var r="";s=s.substr(0,s.length-p.length)+p;for(var c=0;c<s.length;c+=4){var n=(base64inv[s.charAt(c)]<<18)+(base64inv[s.charAt(c+1)]<<12)+
(base64inv[s.charAt(c+2)]<<6)+base64inv[s.charAt(c+3)];r+=String.fromCharCode((n>>>16)&255,(n>>>8)&255,n&255);}
return r.substring(0,r.length-p.length);}
function dbg_toggle(t,id)
{var div=t.previousSibling;while(div.nodeType!=1){div=div.previousSibling};div.style.display=(div.style.display=="block"?"none":"block");t.innerHTML=String.fromCharCode(t.innerHTML==String.fromCharCode(9650)?9660:9650);}
function dbg_append(w,id)
{var div=document.getElementById(id);div.innerHTML=div.innerHTML+base64_decode(w);}
var font_max = 18;
var font_min = 8;
function font_minus(t)
{var tables=t.parentNode.getElementsByTagName('table');for(var i=0;i<tables.length;i++){if(!tables[i].style.fontSize){var size=12;}else{var size=parseInt(tables[i].style.fontSize.replace('px',''));}
if(size>font_min){size-=1;}
tables[i].style.fontSize=size+"px";}}
function font_plus(t)
{var tables=t.parentNode.getElementsByTagName('table');for(var i=0;i<tables.length;i++){if(!tables[i].style.fontSize){var size=12;}else{var size=parseInt(tables[i].style.fontSize.replace('px',''));}
if(size<font_max){size+=1;}
tables[i].style.fontSize=size+"px";}}
</script>
		<?php
		// and finally HTML
		?>
<div id="dbg-wrapper">
	<div id="dbg">
		<a href="#" onclick="font_minus(this);" style="color:white;position:absolute;bottom:0px;right:2px;text-decoration:none;font-weight:bold;">-</a>
		<a href="#" onclick="font_plus(this);" style="color:white;position:absolute;bottom:0px;right:12px;text-decoration:none;font-weight:bold;">+</a>

	</div>
	<a class="toggle-link db-toggle" href="#" onclick="dbg_toggle(this);return false;">&#9650;</a>	
</div>
		<?php
	}
}

class uD extends uDebug{}
?>
