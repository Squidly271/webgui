<?PHP
/* Copyright 2005-2021, Lime Technology
 * Copyright 2012-2021, Bergware International.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 */
?>
<?
$display['font'] = $_COOKIE['fontSize'] ?? $display['font'];
$theme   = strtok($display['theme'],'-');
$header  = $display['header'];
$backgnd = $display['background'];
$themes1 = in_array($theme,['black','white']);
$themes2 = in_array($theme,['gray','azure']);
?>
<!DOCTYPE html>
<html <?=$display['rtl']?>lang="<?=strtok($locale,'_')?:'en'?>">
<head>
<title><?=$var['NAME']?>/<?=$myPage['name']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=1600">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="same-origin">
<link type="image/png" rel="shortcut icon" href="/webGui/images/<?=$var['mdColor']?>.png">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/default-fonts.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/default-cases.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/font-awesome.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/context.standalone.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/jquery.sweetalert.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/default-{$display['theme']}.css")?>">
<link type="text/css" rel="stylesheet" href="<?autov("/webGui/styles/dynamix-{$display['theme']}.css")?>">

<!-- RegWiz -->
<style>
#header {
  z-index: 102 !important;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-pack: justify;
  -ms-flex-pack: justify;
  justify-content: space-between;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
}
vue-userprofile,
unraid-user-profile {
  font-size: 16px;
  margin-left: auto;
  height: 100%;
}

unraid-launchpad {
  position: relative;
  z-index: 10001;
}
</style>
<!-- /RegWiz -->
<style>
<?if ($display['font']):?>
html{font-size:<?=$display['font']?>}
<?endif;?>
<?if ($header):?>
#header,#header .logo,#header .text-right a{color:#<?=$header?>}
#header .block{background-color:transparent}
<?endif;?>
<?if ($backgnd):?>
#header{background-color:#<?=$backgnd?>}
<?if ($themes1):?>
#menu{background-color:#<?=$backgnd?>}
#nav-block #nav-item a{color:#<?=$header?>}
#nav-block #nav-item.active:after{background-color:#<?=$header?>}
<?endif;?>
<?endif;?>
.inline_help{display:none}
.upgrade_notice{position:fixed;top:1px;left:0;width:100%;height:40px;line-height:40px;color:#e68a00;background:#feefb3;border-bottom:#e68a00 1px solid;text-align:center;font-size:1.4rem;z-index:999}
.upgrade_notice i{margin:14px;float:right;cursor:pointer}
.back_to_top{display:none;position:fixed;bottom:30px;right:12px;color:#e22828;font-size:2.5rem;z-index:999}
<?
$safemode = $var['safeMode']=='yes';
$tasks = find_pages('Tasks');
$buttons = find_pages('Buttons');
$banner = '/boot/config/plugins/dynamix/banner.png';
echo "#header.image{background-image:url(";
echo file_exists($banner) ? autov($banner) : '/webGui/images/banner.png';
echo ")}\n";
if ($themes2) {
  foreach ($tasks as $button) if ($button['Code']) echo "#nav-item a[href='/{$button['name']}']:before{content:'\\{$button['Code']}'}\n";
  foreach ($buttons as $button) if ($button['Code']) echo "#nav-item.{$button['name']} a:before{content:'\\{$button['Code']}'}\n";
}
$notes = '/var/tmp/unRAIDServer.txt';
if (!file_exists($notes)) file_put_contents($notes,shell_exec("$docroot/plugins/dynamix.plugin.manager/scripts/plugin changes $docroot/plugins/unRAIDServer/unRAIDServer.plg"));
$notes = "&nbsp;<a href='#' title='"._('View Release Notes')."' onclick=\"openBox('/plugins/dynamix.plugin.manager/include/ShowChanges.php?tmp=1&file=$notes','"._('Release Notes')."',600,900);return false\"><span class='fa fa-info-circle fa-fw'></span></a>"
?>
</style>

<script src="<?autov('/webGui/javascript/dynamix.js')?>"></script>
<script src="<?autov('/webGui/javascript/translate.'.($locale?:'en_US').'.js')?>"></script>
<script>
Shadowbox.init({skipSetup:true});

// server uptime
var uptime = <?=strtok(exec("cat /proc/uptime"),' ')?>;
var expiretime = <?=$var['regTy']=='Trial'||strstr($var['regTy'],'expired')?$var['regTm2']:0?>;
var before = new Date();

// page timer events
var timers = {};

function pauseEvents(id) {
  $.each(timers, function(i,timer){
    if (!id || i==id) clearTimeout(timer);
  });
}
function resumeEvents(id,delay) {
  var startDelay = delay||50;
  $.each(timers, function(i,timer) {
    if (!id || i==id) timers[i] = setTimeout(i+'()', startDelay);
    startDelay += 50;
  });
}
function plus(value,single,plural,last) {
  return value>0 ? (value+' '+(value==1?single:plural)+(last?'':', ')) : '';
}
function updateTime() {
  var now = new Date();
  var days = parseInt(uptime/86400);
  var hour = parseInt(uptime/3600%24);
  var mins = parseInt(uptime/60%60);
  $('span.uptime').html(((days|hour|mins)?plus(days,"<?=_('day')?>","<?=_('days')?>",(hour|mins)==0)+plus(hour,"<?=_('hour')?>","<?=_('hours')?>",mins==0)+plus(mins,"<?=_('minute')?>","<?=_('minutes')?>",true):"<?=_('less than a minute')?>"));
  uptime += Math.round((now.getTime() - before.getTime())/1000);
  before = now;
  if (expiretime > 0) {
    var remainingtime = expiretime - now.getTime()/1000;
    if (remainingtime > 0) {
      days = parseInt(remainingtime/86400);
      hour = parseInt(remainingtime/3600%24);
      mins = parseInt(remainingtime/60%60);
      if (days) {
        $('#licenseexpire').html(plus(days,"<?=_('day')?>","<?=_('days')?>",true)+" <?=_('remaining')?>");
      } else if (hour) {
        $('#licenseexpire').html(plus(hour,"<?=_('hour')?>","<?=_('hours')?>",true)+" <?=_('remaining')?>").addClass('orange-text');
      } else if (mins) {
        $('#licenseexpire').html(plus(mins,"<?=_('minute')?>","<?=_('minutes')?>",true)+" <?=_('remaining')?>").addClass('red-text');
      } else {
        $('#licenseexpire').html("<?=_('less than a minute remaining')?>").addClass('red-text');
      }
    } else {
      $('#licenseexpire').addClass('red-text');
    }
  }
  setTimeout(updateTime,1000);
}
function refresh(top) {
  if (typeof top === 'undefined') {
    for (var i=0,element; element=document.querySelectorAll('input,button,select')[i]; i++) { element.disabled = true; }
    for (var i=0,link; link=document.getElementsByTagName('a')[i]; i++) { link.style.color = "gray"; } //fake disable
    location = location;
  } else {
    $.cookie('top',top,{path:'/'});
    location = location;
  }
}
function initab() {
  $.removeCookie('one',{path:'/'});
  $.removeCookie('tab',{path:'/'});
}
function settab(tab) {
<?switch ($myPage['name']):?>
<?case'Main':?>
  $.cookie('tab',tab,{path:'/'});
<?if ($var['fsState']=='Started'):?>
  $.cookie('one','tab1',{path:'/'});
<?endif;?>
<?break;?>
<?case'Cache':case'Data':case'Flash':case'Parity':?>
  $.cookie('one',tab,{path:'/'});
<?break;?>
<?default:?>
  $.cookie(($.cookie('one')==null?'tab':'one'),tab,{path:'/'});
<?endswitch;?>
}
function done(key) {
  var url = location.pathname.split('/');
  var path = '/'+url[1];
  if (key) for (var i=2; i<url.length; i++) if (url[i]==key) break; else path += '/'+url[i];
  $.removeCookie('one',{path:'/'});
  location.replace(path);
}
function chkDelete(form, button) {
  button.value = form.confirmDelete.checked ? "<?=_('Delete')?>" : "<?=_('Apply')?>";
  button.disabled = false;
}
function openBox(cmd,title,height,width,load,func,id) {
  // open shadowbox window (run in foreground)
  var uri = cmd.split('?');
  var run = uri[0].substr(-4)=='.php' ? cmd+(uri[1]?'&':'?')+'done=<?=urlencode(_("Done"))?>' : '/logging.htm?cmd='+cmd+'&csrf_token=<?=$var["csrf_token"]?>&done=<?=urlencode(_("Done"))?>';
  var options = load ? (func ? {modal:true,onClose:function(){setTimeout(func+'('+'"'+(id||'')+'")',0);}} : {modal:true,onClose:function(){location=location;}}) : {modal:false};
  Shadowbox.open({content:run, player:'iframe', title:title, height:Math.min(height,screen.availHeight), width:Math.min(width,screen.availWidth), options:options});
}
function openWindow(cmd,title,height,width) {
  // open regular window (run in background)
  var window_name = title.replace(/ /g,"_");
  var form_html = '<form action="/logging.htm" method="post" target="'+window_name+'">'+'<input type="hidden" name="csrf_token" value="<?=$var["csrf_token"]?>" />'+'<input type="hidden" name="title" value="'+title+'" />';
  var vars = cmd.split('&');
  form_html += '<input type="hidden" name="cmd" value="'+vars[0]+'">';
  for (var i = 1; i < vars.length; i++) {
    var pair = vars[i].split('=');
    form_html += '<input type="hidden" name="'+pair[0]+'" value="'+pair[1]+'">';
  }
  form_html += '</form>';
  var form = $(form_html);
  $('body').append(form);
  var top = (screen.availHeight-height)/2;
  if (top < 0) {top = 0; height = screen.availHeight;}
  var left = (screen.availWidth-width)/2;
  if (left < 0) {left = 0; width = screen.availWidth;}
  var options = 'resizeable=yes,scrollbars=yes,height='+height+',width='+width+',top='+top+',left='+left;
  window.open('', window_name, options);
  form.submit();
}
function showStatus(name,plugin,job) {
  $.post('/webGui/include/ProcessStatus.php',{name:name,plugin:plugin,job:job},function(status){$(".tabs").append(status);});
}
function showFooter(data, id) {
  if (id !== undefined) $('#'+id).remove();
  $('#copyright').prepend(data);
}
function showNotice(data) {
  $('#user-notice').html(data.replace(/<a>(.*)<\/a>/,"<a href='/Plugins'>$1</a>"));
}

// Banner warning system

var bannerWarnings = [];
var currentBannerWarning = 0;
var bannerWarningInterval = false;
var osUpgradeWarning = false;

function addBannerWarning(text,warning=true,noDismiss=false) {
  var cookieText = text.replace(/[^a-z0-9]/gi,'');
  if ($.cookie(cookieText) == "true") return false;
  if (warning) text = "<i class='fa fa-warning' style='float:initial;'></i> "+text;
  if ( bannerWarnings.indexOf(text) < 0 ) {
    var arrayEntry = bannerWarnings.push("placeholder") - 1;
    if (!noDismiss) text = text + "<a class='bannerDismiss' onclick='dismissBannerWarning("+arrayEntry+",&quot;"+cookieText+"&quot;)'></a>";
    bannerWarnings[arrayEntry] = text;
  } else return bannerWarnings.indexOf(text);

  if (!bannerWarningInterval) {
    showBannerWarnings();
    bannerWarningInterval = setInterval(showBannerWarnings,10000);
  }
  return arrayEntry;
}

function dismissBannerWarning(entry,cookieText) {
  $.cookie(cookieText,"true",{expires:365,path:'/'});
  removeBannerWarning(entry);
}

function removeBannerWarning(entry) {
  bannerWarnings[entry] = false;
  showBannerWarnings();
}

function bannerFilterArray(array) {
  var newArray = [];
  array.filter(function(value,index,arr) {
    if (value) newArray.push(value);
  });
  return newArray;
}

function showBannerWarnings() {
  var allWarnings = bannerFilterArray(Object.values(bannerWarnings));
  if (allWarnings.length == 0) {
    $(".upgrade_notice").hide();
    clearInterval(bannerWarningInterval);
    bannerWarningInterval = false;
    return;
  }
  if (currentBannerWarning >= allWarnings.length) currentBannerWarning = 0;
  $(".upgrade_notice").show().html(allWarnings[currentBannerWarning]);
  currentBannerWarning++;
}

function addRebootNotice(message="<?=_('You must reboot for changes to take effect')?>") {
  addBannerWarning("<i class='fa fa-warning' style='float:initial;'></i> "+message,false,true);
  $.post("/plugins/dynamix.plugin.manager/scripts/PluginAPI.php",{action:'addRebootNotice',message:message});
}

function removeRebootNotice(message="<?=_('You must reboot for changes to take effect')?>") {
  var bannerIndex = bannerWarnings.indexOf("<i class='fa fa-warning' style='float:initial;'></i> "+message);
  if ( bannerIndex < 0 ) {
    return;
  }
  removeBannerWarning(bannerIndex);
  $.post("/plugins/dynamix.plugin.manager/scripts/PluginAPI.php",{action:'removeRebootNotice',message:message});
}

function showUpgrade(text,noDismiss=false) {
  if ($.cookie('os_upgrade')==null) {
    if (osUpgradeWarning) removeBannerWarning(osUpgradeWarning);
    osUpgradeWarning = addBannerWarning(text.replace(/<a>(.*)<\/a>/,"<a href='#' onclick='openUpgrade()'>$1</a>").replace(/<b>(.*)<\/b>/,"<a href='#' onclick='document.rebootNow.submit()'>$1</a>"),false,noDismiss);
  }
}
function hideUpgrade(set) {
  removeBannerWarning(osUpgradeWarning);
  if (set)
    $.cookie('os_upgrade','true',{path:'/'});
  else
    $.removeCookie('os_upgrade',{path:'/'});
}
function openUpgrade() {
  hideUpgrade();
  swal({title:"<?=_('Update')?> Unraid OS",text:"<?=_('Do you want to update to the new version')?>?",type:'warning',html:true,showCancelButton:true,confirmButtonText:"<?=_('Proceed')?>",cancelButtonText:"<?=_('Cancel')?>"},function(){
    openBox("/plugins/dynamix.plugin.manager/scripts/plugin&arg1=update&arg2=unRAIDServer.plg","<?=_('Update')?> Unraid OS",600,900,true);
  });
}
function notifier() {
  var tub1 = 0, tub2 = 0, tub3 = 0;
  $.post('/webGui/include/Notify.php',{cmd:'get'},function(json) {
    if (json && /^<!DOCTYPE html>/.test(json)) {
      // Session is invalid, user has logged out from another tab
      $(location).attr('href','/');
    }
    var data = $.parseJSON(json);
    $.each(data, function(i, notify) {
<?if ($notify['display']):?>
      switch (notify.importance) {
        case 'alert'  : tub1++; break;
        case 'warning': tub2++; break;
        case 'normal' : tub3++; break;
      }
<?else:?>
      $.jGrowl(notify.subject+'<br>'+notify.description, {
        group: notify.importance,
        header: notify.event+': '+notify.timestamp,
        theme: notify.file,
        click: function(e,m,o) { if (notify.link) location=notify.link;},
        beforeOpen: function(e,m,o){if ($('div.jGrowl-notification').hasClass(notify.file)) return(false);},
        beforeClose: function(e,m,o){$.post('/webGui/include/Notify.php',{cmd:'archive',file:notify.file});},
        afterOpen: function(e,m,o){if (notify.link) $(e).css("cursor","pointer");}
      });
<?endif;?>
    });
<?if ($notify['display']):?>
    $('#txt-tub1').removeClass('one two three').addClass(digits(tub1)).text(tub1);
    $('#txt-tub2').removeClass('one two three').addClass(digits(tub2)).text(tub2);
    $('#txt-tub3').removeClass('one two three').addClass(digits(tub3)).text(tub3);
    if (tub1) $('#box-tub1').removeClass('grey-text').addClass('red-text'); else $('#box-tub1').removeClass('red-text').addClass('grey-text');
    if (tub2) $('#box-tub2').removeClass('grey-text').addClass('orange-text'); else $('#box-tub2').removeClass('orange-text').addClass('grey-text');
    if (tub3) $('#box-tub3').removeClass('grey-text').addClass('green-text'); else $('#box-tub3').removeClass('green-text').addClass('grey-text');
<?endif;?>
    timers.notifier = setTimeout(notifier,5000);
  });
}
function digits(number) {
  if (number < 10) return 'one';
  if (number < 100) return 'two';
  return 'three';
}
function openNotifier(filter) {
  $.post('/webGui/include/Notify.php',{cmd:'get'},function(json) {
    var data = $.parseJSON(json);
    $.each(data, function(i, notify) {
			console.log(notify);
      if (notify.importance == filter) {
        $.jGrowl(notify.subject+'<br>'+notify.description, {
          group: notify.importance,
          header: notify.event+': '+notify.timestamp,
          theme: notify.file,
          click: function(e,m,o) { if (notify.link) location=notify.link;},
          beforeOpen: function(e,m,o){if ($('div.jGrowl-notification').hasClass(notify.file)) return(false);},
          beforeClose: function(e,m,o){$.post('/webGui/include/Notify.php',{cmd:'archive',file:notify.file});},
          afterOpen: function(e,m,o){if (notify.link) $(e).css("cursor","pointer");}
        });
      }
    });
  });
}
function closeNotifier(filter) {
  clearTimeout(timers.notifier);
  $.post('/webGui/include/Notify.php',{cmd:'get'},function(json) {
    var data = $.parseJSON(json);
    $.each(data, function(i, notify) {
      if (notify.importance == filter) $.post('/webGui/include/Notify.php',{cmd:'archive',file:notify.file});
    });
    $('div.jGrowl').find('.'+filter).find('div.jGrowl-close').trigger('click');
    setTimeout(notifier,100);
  });
}
function viewHistory(filter) {
  location.replace('/Tools/NotificationsArchive?filter='+filter);
}
$(function() {
  var tab = $.cookie('one')||$.cookie('tab')||'tab1';
  if (tab=='tab0') tab = 'tab'+$('input[name$="tabs"]').length; else if ($('#'+tab).length==0) {initab(); tab = 'tab1';}
  if ($.cookie('help')=='help') {$('.inline_help').show(); $('#nav-item.HelpButton').addClass('active');}
  $('#'+tab).attr('checked', true);
  updateTime();
  $.jGrowl.defaults.closeTemplate = '<i class="fa fa-close"></i>';
  $.jGrowl.defaults.closerTemplate = '<?=$notify['position'][0]=='b' ? '<div class="bottom">':'<div class="top">'?>[ <?=_("close all notifications")?> ]</div>';
  $.jGrowl.defaults.sticky = true;
  $.jGrowl.defaults.check = 100;
  $.jGrowl.defaults.position = '<?=$notify['position']?>';
  $.jGrowl.defaults.themeState = '';
  Shadowbox.setup('a.sb-enable', {modal:true});
});
var mobiles=['ipad','iphone','ipod','android'];
var device=navigator.platform.toLowerCase();
for (var i=0,mobile; mobile=mobiles[i]; i++) {
  if (device.indexOf(mobile)>=0) {$('#footer').css('position','static'); break;}
}
$.ajaxPrefilter(function(s, orig, xhr){
  if (s.type.toLowerCase() == "post" && !s.crossDomain) {
    s.data = s.data || "";
    s.data += s.data?"&":"";
    s.data += "csrf_token=<?=$var['csrf_token']?>";
  }
});

// add any pre-existing reboot notices  
$(function() {
<?$rebootNotice = @file("/tmp/reboot_notifications") ?: [];?>
<?foreach ($rebootNotice as $notice):?>
  var rebootMessage = "<?=trim($notice)?>";
  if ( rebootMessage ) {
    addBannerWarning("<i class='fa fa-warning' style='float:initial;'></i> "+rebootMessage,false,true);
  }
<?endforeach;?>
});
</script>
<? include "/usr/local/emhttp/plugins/dynamix.unraid.net/include/RegWizard.php" ?>
</head>
<body>
 <div id="template">
  <div class="upgrade_notice" style="display:none"></div>
  <div id="header" class="<?=$display['banner']?>">
   <div class="logo">
   <a href="https://unraid.net" target="_blank"><?readfile("$docroot/webGui/images/UN-logotype-gradient.svg")?></a>
   <?=_('Version')?>: <?=$var['version']?><?=$notes?>
   </div>
<!-- RegWiz -->
    <?
    if (file_exists('/boot/config/plugins/Unraid.net/myservers.cfg')) {
      extract(parse_ini_file('/boot/config/plugins/Unraid.net/myservers.cfg',true));
    }

    $serverstate = [
      "deviceCount" => $var['deviceCount'],
      "email" => ($remote['email']) ? $remote['email'] : '',
      "flashproduct" => $var['flashProduct'],
      "flashvendor" => $var['flashVendor'],
      "guid" => $var['flashGUID'],
      "internalip" => $_SERVER['SERVER_ADDR'],
      "internalport" => $_SERVER['SERVER_PORT'],
      "keyfile" => str_replace(['+','/','='], ['-','_',''], trim(base64_encode(@file_get_contents($var['regFILE'])))),
      "protocol" => $_SERVER['REQUEST_SCHEME'],
      "reggen" => (int)$var['regGen'],
      "registered" => empty($remote['apikey']) || empty($var['regFILE']) ? 0 : 1,
      "servername" => $var['NAME'],
      "serverip" => $_SERVER['SERVER_ADDR'],
      "site" => $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'],
      "state" => strtoupper(empty($var['regCheck']) ? $var['regTy'] : $var['regCheck']),
    ];
    // upc translations
    $upc_translations = [
      ($_SESSION['locale']) ? $_SESSION['locale'] : 'en_US' => [
        'getStarted' => _('Get Started'),
        'signIn' => _('Sign In'),
        'signUp' => _('Sign Up'),
        'signOut' => _('Sign Out'),
        'error' => _('Error'),
        'fixError' => _('Fix Error'),
        'closeLaunchpad' => _('Close Launchpad and continue to webGUI'),
        'learnMore' => _('Learn more'),
        'popUp' => _('Pop-up'),
        'close' => _('Close'),
        'backToPopUp' => sprintf(_('Back to %s'), _('Pop-up')),
        'closePopUp' => sprintf(_('Close %s'), _('Pop-up')),
        'contactSupport' => _('Contact Support'),
        'lanIp' => sprintf(_('LAN IP %s'), '{0}'),
        'continueToUnraid' => _('Continue to Unraid'),
        'year' => _('year'),
        'years' => _('years'),
        'month' => _('month'),
        'months' => _('months'),
        'day' => _('day'),
        'days' => _('days'),
        'hour' => _('hour'),
        'hours' => _('hours'),
        'minute' => _('minute'),
        'minutes' => _('minutes'),
        'second' => _('second'),
        'seconds' => _('seconds'),
        'ago' => _('ago'),
        'basicPlusPro' => [
          'heading' => _('Thank you for choosing Unraid OS'),
          'message' => [
            'registered' => _('Get started by signing in to Unraid.net'),
            'upgradeEligible' => _('To support more storage devices as your server grows click Upgrade Key')
          ]
        ],
        'actions' => [
          'purchase' => _('Purchase Key'),
          'upgrade' => _('Upgrade Key'),
          'recover' => _('Recover Key'),
          'replace' => _('Replace Key'),
          'extend' => _('Extend Trial'),
        ],
        'upc' => [
          'avatarAlt' => _("{0} Avatar"),
          'confirmClosure' => _('Confirm closure then continue to webGUI'),
          'closeDropdown' => _('Close dropdown'),
          'openDropdown' => _('Open dropdown'),
          'pleaseConfirmClosureYouHaveOpenPopUp' => _('Please confirm closure. You have an open pop-up.'),
          'trialHasExpiredSeeOptions' => _('Trial has expired see options below'),
          'extraLinks' => [
            'newTab' => sprintf(_('Opens %s in new tab'), '{0}'),
            'myServers' => _('My Servers Dashboard'),
            'forums' => _('Unraid Forums'),
            'settings' => [
              'text' => _('Settings'),
              'title' => _('Settings > Management Access • Unraid.net'),
            ],
          ],
          'meta' => [
            'trial' => [
              'active' => [
                'date' => sprintf(_('Trial key expires at %s'), '{date}'),
                'timeDiff' => sprintf(_('Trial expires in %s'), '{timeDiff}'),
              ],
              'expired' => [
                'date' => sprintf(_('Trial key expired at %s'), '{date}'),
                'timeDiff' => sprintf(_('Trial expired %s'), '{timeDiff}'),
              ],
            ],
            'uptime' => [
              'date' => sprintf(_('Server up since %s'), '{date}'),
              'readable' => sprintf(_('Uptime %s'), '{timeDiff}'),
            ],
          ],
          'myServers' => [
            'heading' => _('My Servers'),
            'beta' => _('beta'),
            'errors' => [
              'unraidApi' => [
                'heading' => _('Unraid API Error'),
                'message' => _('Failed to connect to Unraid API'),
              ],
              'myServers' => [
                'heading' => _('My Servers Error'),
                'message' => _('Please wait a moment and reload the page'),
              ],
            ],
            'closeDetails' => _('Close Details'),
            'loading' => _('Loading My Servers data'),
            'displayingLastKnown' => _('Displaying last known server data'),
            'mothership' => [
              'connected' => _('Connected to Mothership'),
              'notConnected' => _('Not Connected to Mothership'),
            ],
            'accessLabels' => [
              'current' => _('Current server'),
              'local' => _('Local access'),
              'offline' => _('Server Offline'),
              'remote' => _('Remote access'),
              'unavailable' => _('Access unavailable'),
            ],
          ],
          'opensNewHttpsWindow' => [
            'base' => sprintf(_('Opens new HTTPS window to %s'), '{0}'),
            'signIn' => sprintf(_('Opens new HTTPS window to %s'), _('Sign In')),
            'signIn' => sprintf(_('Opens new HTTPS window to %s'), _('Sign Out')),
            'purchase' => sprintf(_('Opens new HTTPS window to %s'), _('Purchase Key')),
            'upgrade' => sprintf(_('Opens new HTTPS window to %s'), _('Upgrade Key')),
          ],
          'signInActions' => [
            'resolve' => _('Sign In to resolve'),
            'purchaseKey' => _('Sign In to Purchase Key'),
            'purchaseKeyOrExtendTrial' => _('@:upc.signInActions.purchaseKey or @:actions.extend'),
          ],
        ],
        'stateData' => [
          'ENOKEYFILE' => [
            'humanReadable' => _('No Keyfile'),
            'heading' => [
              'registered' => _('Thanks for supporting Unraid'),
              'notRegistered' => _("Let's unleash your hardware!"),
            ],
            'message' => [
              'registered' => _('You are all set 👍'),
              'notRegistered' => _('Sign in or sign up to get started'),
            ],
          ],
          'TRIAL' => [
            'humanReadable' => _('Trial'),
            'heading' => _('Thank you for choosing Unraid OS'),
            'message' => _('Your Trial key includes all the functionality and device support of a Pro key.') . ' ' . _('After your Trial has reached expiration your server still functions normally until the next time you Stop the array or reboot your server.') . ' ' . _('At that point you may either purchase a license key or request a Trial extension.'),
            '_extraMsg' => sprintf(_('You have %s remaining on your Trial key'), '{parsedExpireTime}'),
          ],
          'EEXPIRED' => [
            'humanReadable' => _('Trial Expired'),
            'heading' => _('Your Trial has expired'),
            'message' => [
              'base' => _('To continue using Unraid OS you may purchase a license key'),
              'extensionNotEligible' => _('You have used all your Trial extensions') .' @:stateData.EEXPIRED.message.base',
              'extensionEligible' => '@:stateData.EEXPIRED.message.base ' . _('Alternately, you may request a Trial extension'),
            ],
          ],
          'BASIC' => [
            'humanReadable' => _('Basic'),
          ],
          'PLUS' => [
            'humanReadable' => _('Plus'),
          ],
          'PRO' => [
            'humanReadable' => _('Pro'),
          ],
          'EGUID' => [
            'humanReadable' => _('GUID Error'),
            'error' => [
              'heading' => _('Registration key / GUID mismatch'),
              'message' => [
                'default' => _('The license key file does not correspond to the USB Flash boot device. Please copy the correct key file to the bootconfig directory on your USB Flash boot device.'),
                'replacementEligible' => _('@:stateData.EGUID.error.message.default or click Replace Key to transfer your registration to this new flash device.'),
              ],
            ],
          ],
          'ENOKEYFILE2' => [
            'humanReadable' => _('Missing key file'),
            'error' => [
              'heading' => _('@:stateData.ENOKEYFILE2.humanReadable'),
              'message' => _('It appears that your license key file is corrupted or missing. The key file should be located in the bootconfig directory on your USB Flash boot device. If you do not have a backup copy of your license key file you may attempt to recover your key. If this was a Trial installation, you may purchase a license key.'),
            ],
          ],
          'ETRIAL' => [
            'humanReadable' => _('Invalid installation'),
            'error' => [
              'heading' => _('@:stateData.ETRIAL.humanReadable'),
              'message' => _('It is not possible to use a Trial key with an existing Unraid OS installation. You may purchase a license key corresponding to this USB Flash device to continue using this installation.'),
            ],
          ],
          'ENOKEYFILE1' => [
            'humanReadable' => _('No Keyfile'),
            'error' => [
              'heading' => _('No USB flash configuration data'),
              'message' => _('There is a problem with your USB Flash device'),
            ],
          ],
          'ENOFLASH' => [
            'humanReadable' => _('No Flash'),
            'error' => [
              'heading' => _('Cannot access your USB Flash boot device'),
              'message' => _('There is a physical problem accessing your USB Flash boot device'),
            ],
          ],
          'EGUID1' => [
            'humanReadable' => _('Multiple License Keys Present'),
            'error' => [
              'heading' => _('@:stateData.EGUID1.humanReadable'),
              'message' => _('There are multiple license key files present on your USB flash device and none of them correspond to the USB Flash boot device. Please remove all key files except the one you want to replace from the bootconfig directory on your USB Flash boot device. Alternately you may purchase a license key for this USB flash device. If you want to replace one of your license keys with a new key bound to this USB Flash device please first remove all other key files first.'),
            ],
          ],
          'EBLACKLISTED' => [
            'humanReadable' => _('BLACKLISTED'),
            'error' => [
              'heading' => _('Blacklisted USB Flash GUID'),
              'message' => _('This USB Flash boot device has been blacklisted. This can occur as a result of transferring your license key to a replacement USB Flash device, and you are currently booted from your old USB Flash device. A USB Flash device may also be blacklisted if we discover the serial number is not unique – this is common with USB card readers.'),
            ],
          ],
          'EBLACKLISTED1' => [
            'humanReadable' => _('@:stateData.EBLACKLISTED.humanReadable'),
            'error' => [
              'heading' => _('USB Flash device error'),
              'message' => _('This USB Flash device has an invalid GUID. Please try a different USB Flash device.'),
            ],
          ],
          'EBLACKLISTED2' => [
            'humanReadable' => _('@:stateData.EBLACKLISTED.humanReadable'),
            'error' => [
              'heading' => _('USB Flash has no serial number'),
              'message' => _('@:stateData.EBLACKLISTED.error.message'),
            ],
          ],
          'ENOCONN' => [
            'humanReadable' => _('Trial Requires Internet Connection'),
            'error' => [
              'heading' => _('Cannot validate Unraid Trial key'),
              'message' => _('Your Trial key requires an internet connection. Please check Settings > Network.'),
            ],
          ],
          'STALE' => [
            'humanReadable' => _('Stale'),
            'error' => [
              'heading' => _('Stale Server'),
              'message' => _('Please refresh the page to ensure you load your latest configuration'),
            ],
          ],
        ],
        'regWizPopUp' => [
          'regWiz' => _('Registration Wizard'),
          'toHome' => _('To Registration Wizard Home'),
          'continueTrial' => _('Continue Trial'),
          'serverInfoToggle' => _('Toggle server info visibility'),
          'youCanSafelyCloseThisWindow' => _('You can safely close this window'),
          'automaticallyClosingIn' => sprintf(_('Auto closing in %s'), '{0}'),
          'byeBye' => _('bye bye 👋'),
          'browserWillSelfDestructIn' => sprintf(_('Browser will self destruct in %s'), '{0}'),
          'closingPopUpMayLeadToErrors' => _('Closing this pop-up window while actions are being preformed may lead to unintended errors'),
          'goBack' => _('Go Back'),
          'shutDown' => _('Shut Down'),
          'haveAccountSignIn' => _('Already have an account? Sign In'),
          'noAccountSignUp' => _("Don't have an account? Sign Up"),
          'serverInfo' => [
            'flash' => _('Flash'),
            'product' => _('Product'),
            'GUID' => _('GUID'),
            'name' => _('Name'),
            'ip' => _('IP'),
          ],
          'forms' => [
            'displayName' => _('Display Name'),
            'emailAddress' => _('Email Address'),
            'displayNameOrEmailAddress' => _('Display Name or Email Address'),
            'displayNameRootMessage' => _('Use your Unraid.net credentials, not your local server credentials'),
            'honeyPotCopy' => _('If you fill this field out then your email will not be sent'),
            'fieldRequired' => _('This field is required'),
            'submit' => _('Submit'),
            'submitting' => _('Submitting'),
            'notValid' => _('Form not valid'),
            'cancel' => _('Cancel'),
            'confirm' => _('Confirm'),
            'createMyAccount' => _('Create My Account'),
            'subject' => _('Subject'),
            'password' => _('Password'),
            'togglePasswordVisibility' => _('Toggle Password Visibility'),
            'message' => _('Message'),
            'confirmPassword' => _('Confirm Password'),
            'passwordMinimum' => _('8 or more characters'),
            'comments' => _('comments'),
            'newsletterCopy' => _('Sign me up for the monthly Unraid newsletter: a digest of recent blog posts, community videos, popular forum threads, product announcements, and more'),
            'terms' => [
              'iAgree' => _('I agree to the'),
              'text' => _('Terms of Use'),
            ],
          ],
          'routes' => [
            'extendTrial' => [
              'heading' => [
                'loading' => _('Extending Trial'),
                'error' => _('Trial Extension Failed'),
              ],
            ],
            'forgotPassword' => [
              'heading' => _('Forgot Password'),
              'subheading' => _("After resetting your password come back to the Registration Wizard pop-up window to Sign In and complete your server's registration"),
              'resetPasswordNow' => _('Reset Password Now'),
              'backToSignIn' => _('Back to Sign In'),
            ],
            'signIn' => [
              'heading' => [
                'signIn' => _('Unraid.net Sign In'),
                'recover' => _('Unraid.net Sign In to Recover Key'),
                'replace' => _('Unraid.net Sign In to Replace Key'),
              ],
              'subheading' => _('Please sign in with your Unraid.net forum account'),
              'form' => [
                'replacementConditions' => [
                  'name' => _('Acknowledge Replacement Conditions'),
                  'label' => _('I acknowledge that replacing a license key results in permanently blacklisting the previous USB Flash GUID'),
                ],
                'label' => [
                  'password' => [
                    'replace' => _('Unraid.net account password'),
                  ],
                ],
              ],
            ],
            'signUp' => [
              'heading' => _('Sign Up for Unraid.net'),
              'subheading' => _('This setup will help you get your server up and running'),
            ],
            'signOut' => [
              'heading' => _('Unraid.net Sign Out'),
            ],
            'success' => [
              'heading' => [
                'username' => sprintf(_('Hi %s'), '{0}'),
                'default' => _('Success'),
              ],
              'subheading' => [
                'extention' => _('Your trial will expire in 15 days'),
                'newTrial' => _('Your trial will expire in 30 days'),
              ],
              'signIn' => [
                'tileTitle' => [
                  'actionFail' => sprintf(_('%s was not signed in to your Unraid.net account'), '{0}'),
                  'actionSuccess' => sprintf(_('%s is signed in to your Unraid.net account'), '{0}'),
                  'loading' => sprintf(_('Signing in %s to Unraid.net account'), '{0}'),
                ],
              ],
              'signOut' => [
                'tileTitle' => [
                  'actionFail' => sprintf(_('%s was not signed out of your Unraid.net account'), '{0}'),
                  'actionSuccess' => sprintf(_('%s was signed out of your Unraid.net account'), '{0}'),
                  'loading' => sprintf(_('Signing out %s from Unraid.net account'), '{0}'),
                ],
              ],
              'keys' => [
                'trial' => _('Trial'),
                'basic' => _('Basic'),
                'plus' => _('Plus'),
                'pro' => _('Pro'),
              ],
              'extended' => sprintf(_('%s Key Extended'), '{0}'),
              'recovered' => sprintf(_('%s Key Recovered'), '{0}'),
              'replaced' => sprintf(_('%s Key Replaced'), '{0}'),
              'created' => sprintf(_('%s Key Created'), '{0}'),
              'install' => [
                'loading' => sprintf(_('Installing %s Key'), '{0}'),
                'error' => sprintf(_('%s Key Install Error'), '{0}'),
                'success' => sprintf(_('Installed %s Key'), '{0}'),
              ],
              'timeout' => sprintf(_('Communication with %s has timed out'), '{0}'),
              'loading1' => _('Please keep this window open'),
              'loading2' => _('Were working our magic'),
              'countdown' => [
                'success' => [
                  'prefix' => sprintf(_('Auto closing in %s'), '{0}'),
                  'text' => _('You can safely close this window'),
                ],
                'error' => [
                  'prefix' => sprintf(_('Auto redirecting in %s'), '{0}'),
                  'text' => _('Back to Registration Home'),
                  'complete' => _('Back in a flash ⚡️'),
                ],
              ],
            ],
            'troubleshoot' => [
              'heading' => [
                'default' => _('Troubleshoot'),
                'success' => _('Thank you for contacting Unraid'),
              ],
              'subheading' => [
                'default' => _("Forgot what Unraid.net account you used? Have a USB flash device that already has an account associated with it? Just give us the details about what happened and we'll do our best to get you up and running again."),
                'success' => _('We have received your e-mail and will respond in the order it was received. While we strive to respond to all requests as quickly as possible please allow for up to 3 business days for a response.'),
              ],
              'relevantServerData' => _('Your USB Flash GUID and other relevant server data will also be sent'),
            ],
            'verifyEmail' => [
              'heading' => _('Verify Email'),
              'form' => [
                'verificationCode' => _('verification code'),
                'verifyCode' => _('Paste or Enter code'),
              ],
              'noCode' => _("Didn't get code?"),
            ],
            'whatIsUnraidNet' => [
              'heading' => _('What is Unraid.net?'),
              'subheading' => _('Expand your servers capabilities'),
              'copy' => _('With an Unraid.net account you can start using My Servers (beta) which gives you access to the following features:'),
              'features' => [
                'secureRemoteAccess' => [
                  'heading' => _('Secure remote access'),
                  'copy' => _("Whether you need to add a share container or virtual machine do it all from the webGui from anytime and anywhere using HTTPS. Best of all all SSL certificates are verified by Let's Encrypt so no browser security warnings."),
                ],
                'realTimeMonitoring' => [
                  'heading' => _('Real-time Monitoring'),
                  'copy' => _('Get quick real-time info on the status of your servers such as storage container and VM usage. And not just for one server but all the servers in your Unraid fleet'),
                ],
                'usbFlashBackup' => [
                  'heading' => _('USB Flash Backup'),
                  'copy' => _('Click a button and your flash is automatically backed up to Unraid.net enabling easy recovery in the event of a device failure. Never self-managehost your flash backups again'),
                ],
                'regKeyManagement' => [
                  'heading' => _('Registration key management'),
                  'copy' => _('Download any registration key linked to your account. Upgrade keys to higher editions.'),
                ],
              ],
            ],
            'notFound' => [
              'subheading' => _('Page Not Found'),
            ],
            'notAllowed' => [
              'subheading' => _('Page Not Allowed'),
            ],
          ],
        ],
      ],
    ];
    ?>
    <unraid-user-profile
      apikey="<?=($remote['apikey']) ? $remote['apikey'] : ''?>"
      banner="<?=($display['banner']) ? $display['banner'] : ''?>"
      bgcolor="<?=($backgnd) ? '#'.$backgnd : ''?>"
      displaydesc="<?=($display['headerdescription']!='no') ? 'true' : ''?>"
      expiretime="<?=1000*($var['regTy']=='Trial'||strstr($var['regTy'],'expired')?$var['regTm2']:0)?>"
      locale="<?=($_SESSION['locale']) ? $_SESSION['locale'] : 'en_US'?>"
      locale-messages="<?=rawurlencode(json_encode($upc_translations, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE))?>"
      reg-wiz-time="<?=($remote['regWizTime']) ? $remote['regWizTime'] : ''?>"
      send-crash-info="<?=$remote['sendCrashInfo']?>"
      serverdesc="<?=$var['COMMENT']?>"
      servermodel="<?=$var['SYS_MODEL']?>"
      serverstate="<?=rawurlencode(json_encode($serverstate, JSON_UNESCAPED_SLASHES))?>"
      textcolor="<?=($header) ? '#'.$header : ''?>"
      theme="<?=$display['theme']?>"
      uptime="<?=1000*(time() - round(strtok(exec("cat /proc/uptime"),' ')))?>"
      ></unraid-user-profile>
  </div>
  <a href="#" class="back_to_top" title="<?=_('Back To Top')?>"><i class="fa fa-arrow-circle-up"></i></a>
<?
// Build page menus
echo "<div id='menu'><div id='nav-block'><div id='nav-left'>";
foreach ($tasks as $button) {
  $page = $button['name'];
  echo "<div id='nav-item'";
  echo $task==$page ? " class='active'>" : ">";
  echo "<a href='/$page' onclick='initab()'>"._($button['Name'] ?? $page)."</a></div>";
}
unset($tasks);
if ($display['usage']) my_usage();
echo "</div>";
echo "<div id='nav-right'>";
foreach ($buttons as $button) {
  eval('?>'.parse_text($button['text']));
  if (empty($button['Link'])) {
    $icon = $button['Icon'];
    if (substr($icon,-4)=='.png') {
      $icon = "<img src='/{$button['root']}/icons/$icon' class='system'>";
    } elseif (substr($icon,0,5)=='icon-') {
      $icon = "<i class='$icon system'></i>";
    } else {
      if (substr($icon,0,3)!='fa-') $icon = "fa-$icon";
      $icon = "<i class='fa $icon system'></i>";
    }
    $title = $themes2 ? "" : " title=\""._($button['Title'])."\"";
    echo "<div id='nav-item' class='{$button['name']} util'><a href='".($button['Href'] ?? '#')."' onclick='{$button['name']}();return false;'{$title}>$icon<span>"._($button['Title'])."</span></a></div>";
  } else
    echo "<div id='{$button['Link']}'></div>";
}
unset($buttons,$button);
if ($notify['display']) {
  echo "<span id='nav-tub1' class='tub'><i id='box-tub1' class='fa fa-square grey-text'></i><span id='txt-tub1' class='score one'>0</span></span>";
  echo "<span id='nav-tub2' class='tub'><i id='box-tub2' class='fa fa-square grey-text'></i><span id='txt-tub2' class='score one'>0</span></span>";
  echo "<span id='nav-tub3' class='tub'><i id='box-tub3' class='fa fa-square grey-text'></i><span id='txt-tub3' class='score one'>0</span></span>";
}
echo "</div></div></div>";

// Build page content
echo "<div class='tabs'>";
$tab = 1;
$view = $myPage['name'];
$pages = [];
if ($myPage['text']) $pages[$view] = $myPage;
if ($myPage['Type']=='xmenu') $pages = array_merge($pages, find_pages($view));
if (isset($myPage['Tabs'])) $display['tabs'] = strtolower($myPage['Tabs'])=='true' ? 0 : 1;
$tabbed = $display['tabs']==0 && count($pages)>1;

foreach ($pages as $page) {
  $close = false;
  if (isset($page['Title'])) {
    eval("\$title=\"".htmlspecialchars($page['Title'])."\";");
    if ($tabbed) {
      echo "<div class='tab'><input type='radio' id='tab{$tab}' name='tabs' onclick='settab(this.id)'><label for='tab{$tab}'>";
      echo tab_title($title,$page['root'],$page['Tag']??false);
      echo "</label><div class='content'>";
      $close = true;
    } else {
      if ($tab==1) echo "<div class='tab'><input type='radio' id='tab{$tab}' name='tabs'><div class='content shift'>";
      echo "<div id='title'><span class='left'>";
      echo tab_title($title,$page['root'],$page['Tag']??false);
      echo "</span></div>";
    }
    $tab++;
  }
  if (isset($page['Type']) && $page['Type']=='menu') {
    $pgs = find_pages($page['name']);
    foreach ($pgs as $pg) {
      @eval("\$title=\"".htmlspecialchars($pg['Title'])."\";");
      $link = "$path/{$pg['name']}";
      $icon = $pg['Icon'] ?? "<i class='icon-app PanelIcon'></i>";
      if (substr($icon,-4)=='.png') {
        $root = $pg['root'];
        if (file_exists("$docroot/$root/images/$icon")) {
          $icon = "<img src='/$root/images/$icon' class='PanelImg'>";
        } elseif (file_exists("$docroot/$root/$icon")) {
          $icon = "<img src='/$root/$icon' class='PanelImg'>";
        } else {
          $icon = "<i class='icon-app PanelIcon'></i>";
        }
      } elseif (substr($icon,0,5)=='icon-') {
        $icon = "<i class='$icon PanelIcon'></i>";
      } elseif ($icon[0]!='<') {
        if (substr($icon,0,3)!='fa-') $icon = "fa-$icon";
        $icon = "<i class='fa $icon PanelIcon'></i>";
      }
      echo "<div class=\"Panel\"><a href=\"$link\" onclick=\"$.cookie('one','tab1',{path:'/'})\"><span>$icon</span><div class=\"PanelText\">"._($title)."</div></a></div>";
    }
  }
  empty($page['Markdown']) || $page['Markdown']=='true' ? eval('?>'.Markdown(parse_text($page['text']))) : eval('?>'.parse_text($page['text']));
  if ($close) echo "</div></div>";
}
unset($pages,$page,$pgs,$pg,$icon);
?>
</div></div>
<div class="spinner fixed"></div>
<form name="rebootNow" method="POST" action="/webGui/include/Boot.php"><input type="hidden" name="cmd" value="reboot"></form>
<iframe id="progressFrame" name="progressFrame" frameborder="0"></iframe>
<?
// Build footer
echo '<div id="footer"><span id="statusraid"><span id="statusbar">';
$progress = ($var['fsProgress']!='')? "&bullet;<span class='blue strong'>{$var['fsProgress']}</span>" : '';
switch ($var['fsState']) {
case 'Stopped':
  echo "<span class='red strong'><i class='fa fa-stop-circle'></i> "._('Array Stopped')."</span>$progress"; break;
case 'Starting':
  echo "<span class='orange strong'><i class='fa fa-pause-circle'></i> "._('Array Starting')."</span>$progress"; break;
case 'Stopping':
  echo "<span class='orange strong'><i class='fa fa-pause-circle'></i> "._('Array Stopping')."</span>$progress"; break;
default:
  echo "<span class='green strong'><i class='fa fa-play-circle'></i> "._('Array Started')."</span>$progress"; break;
}
echo "</span></span><span id='countdown'></span><span id='user-notice' class='red-text'></span>";
echo "<span id='copyright'>Unraid&reg; webGui &copy;2020, Lime Technology, Inc.";
echo " <a href='http://lime-technology.com/wiki/index.php/Official_Documentation' target='_blank' title=\""._('Online manual')."\"><i class='fa fa-book'></i> "._('manual')."</a>";
echo "</span></div>";
?>
<script>
// Firefox specific workaround
if (typeof InstallTrigger!=='undefined') $('#nav-block').addClass('mozilla');

function parseINI(data){
  var regex = {
    section: /^\s*\[\s*\"*([^\]]*)\s*\"*\]\s*$/,
    param: /^\s*([^=]+?)\s*=\s*\"*(.*?)\s*\"*$/,
    comment: /^\s*;.*$/
  };
  var value = {};
  var lines = data.split(/[\r\n]+/);
  var section = null;
  lines.forEach(function(line) {
    if (regex.comment.test(line)) {
      return;
    } else if (regex.param.test(line)) {
      var match = line.match(regex.param);
      if (section) {
        value[section][match[1]] = match[2];
      } else {
        value[match[1]] = match[2];
      }
    } else if (regex.section.test(line)) {
      var match = line.match(regex.section);
      value[match[1]] = {};
      section = match[1];
    } else if (line.length==0 && section) {
      section = null;
    };
  });
  return value;
}
// unraid animated logo
var unraid_logo = '<?readfile("$docroot/webGui/images/animated-logo.svg")?>';

var watchdog = new NchanSubscriber('/sub/var');
watchdog.on('message', function(data) {
  var ini = parseINI(data);
  var state = ini['fsState'];
  var progress = ini['fsProgress'];
  var status;
  if (state=='Stopped') {
    status = "<span class='red strong'><i class='fa fa-stop-circle'></i> <?=_('Array Stopped')?></span>";
  } else if (state=='Started') {
    status = "<span class='green strong'><i class='fa fa-play-circle'></i> <?=_('Array Started')?></span>";
  } else if (state=='Formatting') {
    status = "<span class='green strong'><i class='fa fa-play-circle'></i> <?=_('Array Started')?></span>&bullet;<span class='orange strong'><?=_('Formatting device(s)')?></span>";
  } else {
    status = "<span class='orange strong'><i class='fa fa-pause-circle'></i> "+_('Array '+state)+"</span>";
  }
  if (ini['mdResyncPos']>0) {
    var action;
    if (ini['mdResyncAction'].indexOf("recon")>=0) action = "<?=_('Parity-Sync / Data-Rebuild')?>";
    else if (ini['mdResyncAction'].indexOf("clear")>=0) action = "<?=_('Clearing')?>";
    else if (ini['mdResyncAction']=="check") action = "<?=_('Read-Check')?>";
    else if (ini['mdResyncAction'].indexOf("check")>=0) action = "<?=_('Parity-Check')?>";
    action += " "+(ini['mdResyncPos']/(ini['mdResyncSize']/100+1)).toFixed(1)+" %";
    status += "&bullet;<span class='orange strong'>"+action.replace('.','<?=$display['number'][0]?>')+"</span>";
    if (ini['mdResync']==0) status += "(<?=_('Paused')?>)";
  }
  if (progress) status += "&bullet;<span class='blue strong'>"+_(progress)+"</span>";
  $('#statusbar').html(status);
});
var backtotopoffset = 250;
var backtotopduration = 500;
$(window).scroll(function() {
  if ($(this).scrollTop() > backtotopoffset) {
    $('.back_to_top').fadeIn(backtotopduration);
  } else {
    $('.back_to_top').fadeOut(backtotopduration);
  }
<?if ($themes1):?>
  var top = $('div#header').height()-1; // header height has 1 extra pixel to cover overlap
  $('div#menu').css($(this).scrollTop() > top ? {position:'fixed',top:'0'} : {position:'absolute',top:top+'px'});
<?endif;?>
});
$('.back_to_top').click(function(event) {
  event.preventDefault();
  $('html,body').animate({scrollTop:0},backtotopduration);
  return false;
});
$(function() {
  $('div.spinner.fixed').html(unraid_logo);
  setTimeout(function(){$('div.spinner').not('.fixed').each(function(){$(this).html(unraid_logo);});},500); // display animation if page loading takes longer than 0.5s
  shortcut.add('F1',function(){HelpButton();});
<?if ($var['regTy']=='unregistered'):?>
  $('#licensetype').addClass('orange-text');
<?elseif (!in_array($var['regTy'],['Trial','Basic','Plus','Pro'])):?>
  $('#licensetype').addClass('red-text');
<?endif;?>
<?if ($notify['entity'] & 1 == 1):?>
  $.post('/webGui/include/Notify.php',{cmd:'init'},function(){timers.notifier = setTimeout(notifier,0);});
<?endif;?>
  $('input[value="<?=_("Apply")?>"],input[value="Apply"],input[name="cmdEditShare"],input[name="cmdUserEdit"]').prop('disabled',true);
  $('form').find('select,input[type=text],input[type=number],input[type=password],input[type=checkbox],input[type=radio],input[type=file],textarea').each(function(){$(this).on('input change',function() {
    var form = $(this).parentsUntil('form').parent();
    form.find('input[value="<?=_("Apply")?>"],input[value="Apply"],input[name="cmdEditShare"],input[name="cmdUserEdit"]').not('input.lock').prop('disabled',false);
    form.find('input[value="<?=_("Done")?>"],input[value="Done"]').not('input.lock').val("<?=_('Reset')?>").prop('onclick',null).off('click').click(function(){refresh(form.offset().top)});
  });});

  var top = ($.cookie('top')||0) - $('.tabs').offset().top - 75;
  if (top>0) {$('html,body').scrollTop(top);}
  $.removeCookie('top',{path:'/'});
<?if ($safemode):?>
  showNotice("<?=_('System running in')?> <b><?=('safe mode')?></b>");
<?else:?>
<?$readme = @file_get_contents("$docroot/plugins/unRAIDServer/README.md",false,null,0,20);?>
<?if (strpos($readme,'REBOOT REQUIRED')!==false):?>
  showUpgrade("<b><?=_('Reboot Now')?></b> <?=_('to upgrade Unraid OS')?>",true);
<?elseif (strpos($readme,'DOWNGRADE')!==false):?>
  showUpgrade("<b><?=_('Reboot Now')?></b> <?=_('to downgrade Unraid OS')?>",true);
<?elseif ($version = plugin_update_available('unRAIDServer',true)):?>
  showUpgrade("Unraid OS v<?=$version?> <?=_('is available')?>. <a><?=_('Update Now')?></a>");
<?endif;?>
<?if (!$notify['system']):?>
  addBannerWarning("<?=_('System notifications are')?> <b><?=_('disabled')?></b>. <?=_('Click')?> <a href='/Settings/Notifications' style='cursor:pointer'><?=_('here')?></a> <?=_('to change notification settings')?>.",true,true);
<?endif;?>
<?endif;?>
<?if ($notify['display']):?>
  var opts = [];
  context.init({preventDoubleContext:false,left:true,above:false});
  opts.push({text:"<?=_('View')?>",icon:'fa-folder-open-o',action:function(e){e.preventDefault();openNotifier('alert');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('History')?>",icon:'fa-file-text-o',action:function(e){e.preventDefault();viewHistory('alert');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('Acknowledge')?>",icon:'fa-check-square-o',action:function(e){e.preventDefault();closeNotifier('alert');}});
  context.attach('#nav-tub1',opts);

  var opts = [];
  context.init({preventDoubleContext:false,left:true,above:false});
  opts.push({text:"<?=_('View')?>",icon:'fa-folder-open-o',action:function(e){e.preventDefault();openNotifier('warning');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('History')?>",icon:'fa-file-text-o',action:function(e){e.preventDefault();viewHistory('warning');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('Acknowledge')?>",icon:'fa-check-square-o',action:function(e){e.preventDefault();closeNotifier('warning');}});
  context.attach('#nav-tub2',opts);

  var opts = [];
  context.init({preventDoubleContext:false,left:true,above:false});
  opts.push({text:"<?=_('View')?>",icon:'fa-folder-open-o',action:function(e){e.preventDefault();openNotifier('normal');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('History')?>",icon:'fa-file-text-o',action:function(e){e.preventDefault();viewHistory('normal');}});
  opts.push({divider:true});
  opts.push({text:"<?=_('Acknowledge')?>",icon:'fa-check-square-o',action:function(e){e.preventDefault();closeNotifier('normal');}});
  context.attach('#nav-tub3',opts);
<?endif;?>
  if (location.pathname.search(/\/(AddVM|UpdateVM|AddContainer|UpdateContainer)/)==-1) {
    $('blockquote.inline_help').each(function(i) {
      $(this).attr('id','helpinfo'+i);
      var pin = $(this).prev();
      if (!pin.prop('nodeName')) pin = $(this).parent().prev();
      while (pin.prop('nodeName') && pin.prop('nodeName').search(/(table|dl)/i)==-1) pin = pin.prev();
      pin.find('tr:first,dt:last').each(function() {
        var node = $(this);
        var name = node.prop('nodeName').toLowerCase();
        if (name=='dt') {
          while (!node.html() || node.html().search(/(<input|<select|nbsp;)/i)>=0 || name!='dt') {
            if (name=='dt' && node.is(':first-of-type')) break;
            node = node.prev();
            name = node.prop('nodeName').toLowerCase();
          }
          node.css('cursor','help').click(function(){$('#helpinfo'+i).toggle('slow');});
        } else {
          if (node.html() && (name!='tr' || node.children('td:first').html())) node.css('cursor','help').click(function(){$('#helpinfo'+i).toggle('slow');});
        }
      });
    });
  }
  $('form').append($('<input>').attr({type:'hidden', name:'csrf_token', value:'<?=$var['csrf_token']?>'}));
  watchdog.start();
});
</script>
</body>
</html>
