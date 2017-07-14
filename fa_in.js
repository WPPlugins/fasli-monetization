set_cookie=function(name,value,expire)
{if(typeof value!='undefined')
{if(value===null)
{value='';}
var cookie_expire='';if(expire===null)
{expire=new Date();expire.setTime(expire.getTime()+(24*60*60*1000));}
cookie_expire='; expires='+ expire.toUTCString();var cookie_path='; path=/';var cookie_domain='; domain='+ window.location.hostname;var cookie_secure='';document.cookie=[name,'=',encodeURIComponent(value),cookie_expire,cookie_path,cookie_domain,cookie_secure].join('');}};get_cookie=function(name)
{var cookie_value=null;if(document.cookie&&document.cookie!=''){var cookies=document.cookie.split(';');for(var i=0;i<cookies.length;i++){var cookie=jQuery.trim(cookies[i]);if(cookie.substring(0,name.length+ 1)==(name+'=')){cookie_value=decodeURIComponent(cookie.substring(name.length+ 1));break;}}}
return cookie_value;}
document.write('<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>');document.write('<script type="text/javascript" src="//fas.li/js/fa_go.js"></script>');
