<?php


use App\Models\TmdbLogic;
use Bootstrap\Libs\DB;


// Redidect Page
function showMessage($url, $message1,$refresh = 3)
{
    if ($url != URLROOT) {
        $url = URLROOT . '/' . $url;
    }

    $data = [
        'TITLE' => 'Message',
        'MESSAGE' => $message1,
        'URL' => $url,
        'REFRESH'  => $refresh
    ];
    extract($data);
    require_once BASEROOT . "/page/user/error-page.php";
    exit();
}
/*
// Get Redirect
function Url($url = URLROOT, $go_to = false)
{
    if ($url != URLROOT) {
        $url = URLROOT . '/' . $url;
    }

    if ($go_to) {
        if (!headers_sent()) {
            header("Location: " . $url, true, 302);
            exit();
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="' . $url . '";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="3; url=' . $url . '" />';
            echo '</noscript>';
            exit();
        }
    }

    return $url;
}

*/

// Create Random String
function mksecret($len = 20)
{
    $str = null;
    $chars = array_merge(range(0, 9), range("A", "Z"), range("a", "z"));
    shuffle($chars);
    $x = count($chars) - 1;
    for ($i = 1; $i <= $len; $i++) {
        $str .= $chars[mt_rand(0, $x)];
    }
    return $str;
}

// Smilies Array
$emoji = array(
    "😊" => "smile",
    "😞" => "sad",
    "😉" => "wink",
    "🤩" => "grinstar",
    "😐" => "plain",
    "😕" => "confused",
    "🤓" => "glasses",
    "😬" => "eek",
    "😎" => "cool",
    "😢" => "crying",
    "😘" => "kiss",
    "😇" => "angel",
    "😈" => "devilish",
    "🤣" => "roflmao",
    "😴" => "sleep",
    "🥰" => "love",
    '💩' => "poop",
    "😲" => "shocked",
    "😡" => "mad",
    "🙄" => "rolleyes",
    "🤢" => "sick",
    "😂" => "crylaugh",
    "🔥" => "fire",
    "🦄" => "unicorn",
    "😺" => "cat",
    "😀" => "grin",
    "👍" => "thumbsup",
    "👎" => "thumbsdown",
    "👌" => 'ok',
    "🙈" => "monkey",
    "🆘" => "help",
    "⚠" => "warn",
    "💣" => "bomb",
    "💡" => "idea",
);

// $form = Form Name / $name = Name of Text Area (Textarea) / $content = Content Textarea (Only to edit Pages, etc ...)
// Function To Display Input Box With BBCodes And Smilies
function textbbcode($form, $name, $content = "")
{
    // Includen JS Function For BBCode
    require "assets/js/BBTag.js"; ?>

    <div class="row justify-content-md-center">
    <div class="col-12  text-center"> <?php

    // bbcode
    print("<i class='p-1 fa fa-bold' id='BBCode' name='Bold' style='height:20px; width:20px;' onclick=\"bbcomment('[b]', '[/b]')\" title='Bold' /></i>");
    print("<i class='p-1 fa fa-italic' id='BBCode' name='Italic' style='height:20px; width:20px;' onclick=\"bbcomment('[i]', '[/i]')\" title='Italic' /></i>");
    print("<i class='p-1 fa fa-underline' id='BBCode' name='Highlight' style='height:20px; width:20px;' onclick=\"bbcomment('[u]', '[/u]')\" title='Highlight'/></i>");
    print("<i class='p-1 fa fa-strikethrough' id='BBCode' name='Strike' style='height:20px; width:20px;' onclick=\"bbcomment('[s]', '[/s]')\" title='Strike' /></i>");
    print("<i class='p-1 fa fa-list' id='BBCode' name='List' style='height:20px; width:20px;' onclick=\"bbcomment('[list]', '[/list]')\" title='List'	/></i>");
    print("<i class='p-1 fa fa-quote-right' id='BBCode' name='Quote' style='height:20px; width:20px;'	onclick=\"bbcomment('[quote]', '[/quote]')\" title='Quote'	/></i>");
    print("<i class='p-1 fa fa-code' id='BBCode' name='Code' style='height:20px; width:20px;' onclick=\"bbcomment('[code]', '[/code]')\" title='Code' /></i>");
    print("<i class='p-1 fa fa-link' id='BBCode' name='Url' style='height:20px; width:20px;' onclick=\"bbcomment('[url]', '[/url]')\"	title='Link' /></i>");
    print("<i class='p-1 fa fa-bolt' id='BBCode' name='scroller' style='height:20px; width:20px;'	onclick=\"bbcomment('[df]', '[/df]')\" title='scroller' /></i>");
    print("<i class='p-1 fa fa-arrow-left' id='BBCode' name='Align Leftt' style='height:20px; width:20px;' onclick=\"bbcomment('[align=left]','[/align]')\" title='Align Left' /></i>");
    print("<i class='p-1 fa fa-align-center' id='BBCode' name='Align Center' style='height:20px; width:20px;' onclick=\"bbcomment('[align=center]','[/align]')\" title='Align Center' /></i>");
    print("<i class='p-1 fa fa-arrow-right' id='BBCode' name='Align Right' style='height:20px; width:20px;'	onclick=\"bbcomment('[align=right]','[/align]')\" title='Align Right' /></i>");
    print("<i class='p-1 fa fa-picture-o' id='BBCode' name='Image' style='height:20px; width:20px;' onclick=\"bbcomment('[img]', '[/img]')\" title='Image' /></i>");
    print("<i class='p-1 fa fa-video-camera' id='BBCode' name='Video' style='height:20px; width:20px;' onclick=\"bbcomment('[video]', '[/video]')\"  title='Video' /></i>");
    print("<i class='p-1 fa fa-ban' id='BBCode' name='Hide' style='height:20px; width:20px;' onclick=\"bbcomment('[hide]','[/hide]')\"  title='Hide' /></i>");
    print("<a href='https://imgur.com/upload class='p-1'' /><img src='" . Url('assets/images/misc/bbcode_imgur.webp') . "' class='p-1' style='margin-bottom:4px;height:20px;width:20px;' title='Upload Image' /></a>");
    print("<a href='http://www.youtube.com' class='p-1' /><img src='" . Url('assets/images/misc/bbcode_youtube.webp') . "' class='p-1'  style='margin-bottom:4px;height:20px;width:20px;' title='Youtube' /></a>");
    
    // Smiley
    print("<a data-bs-toggle='collapse' data-bs-target='#collapseExample' aria-expanded='false' aria-controls='collapseExample'><img class='p-1' style='margin-bottom: 4px' src='" . Url('assets/images/misc/grin.webp') . "' alt='' /></a>&nbsp;&nbsp;");
    
    // colour
    print("<select name='color' onChange='bbcouleur(this.value);' title='Colour'>");
    print("<option value='0' name='color'>Colour</option>");
    print("<option value='#000000' style='BACKGROUND-COLOR:#000000'>Black</option>");
    print("<option value='#686868' style='BACKGROUND-COLOR:#686868'>Grey</option>");
    print("<option value='#708090' style='BACKGROUND-COLOR:#708090'>Dark Grey</option>");
    print("<option value='#C0C0C0' style='BACKGROUND-COLOR:#C0C0C0'>Light Grey</option>");
    print("<option value='#FFFFFF' style='BACKGROUND-COLOR:#FFFFFF'>White</option>");
    print("<option value='#FFFFE0' style='BACKGROUND-COLOR:#FFFFE0'>Beech</option>");
    print("<option value='#880000' style='BACKGROUND-COLOR:#880000'>Dark Red</option>");
    print("<option value='#B82428' style='BACKGROUND-COLOR:#B82428'>Light Red</option>");
    print("<option value='#FF0000' style='BACKGROUND-COLOR:#FF0000'>Red</option>");
    print("<option value='#FF1490' style='BACKGROUND-COLOR:#FF1490'>Dark Pink</option>");
    print("<option value='#FF68B0' style='BACKGROUND-COLOR:#FF68B0'>Pink</option>");
    print("<option value='#FFC0C8' style='BACKGROUND-COLOR:#FFC0C8'>Light Pink</option>");
    print("<option value='#FF4400' style='BACKGROUND-COLOR:#FF4400'>Dark Orange</option>");
    print("<option value='#FF6448' style='BACKGROUND-COLOR:#FF6448'>Redish Orange</option>");
    print("<option value='#FFA800' style='BACKGROUND-COLOR:#FFA800'>Orange</option>");
    print("<option value='#FFD800' style='BACKGROUND-COLOR:#FFD800'>Dark Yellow</option>");
    print("<option value='#cfbc38' style='BACKGROUND-COLOR:#cfbc38'>Yellow</option>");
    print("<option value='#FF00FF' style='BACKGROUND-COLOR:#FF00FF'>Light Purple</option>");
    print("<option value='#C01480' style='BACKGROUND-COLOR:#C01480'>Dark Purple</option>");
    print("<option value='#B854D8' style='BACKGROUND-COLOR:#B854D8'>Dark Violet</option>");
    print("<option value='#D8A0D8' style='BACKGROUND-COLOR:#D8A0D8'>Light Violet</option>");
    print("<option value='#000080' style='BACKGROUND-COLOR:#000080'>Darkest Blue</option>");
    print("<option value='#0000FF' style='BACKGROUND-COLOR:#0000FF'>Dark Blue</option>");
    print("<option value='#2090FF' style='BACKGROUND-COLOR:#2090FF'>Ble</option>");
    print("<option value='#00BCFF' style='BACKGROUND-COLOR:#00BCFF'>Light Blue</option>");
    print("<option value='#B0E0E8' style='BACKGROUND-COLOR:#B0E0E8'>Faint Blue</option>");
    print("<option value='#A02828' style='BACKGROUND-COLOR:#A02828'>Brown</option>");
    print("<option value='#F0A460' style='BACKGROUND-COLOR:#F0A460'>Brown Creme</option>");
    print("<option value='#D0B488' style='BACKGROUND-COLOR:#D0B488'>Light Brown</option>");
    print("<option value='#B8B868' style='BACKGROUND-COLOR:#B8B868'>Brown/Green</option>");
    print("<option value='#008000' style='BACKGROUND-COLOR:#008000'>Dark Green</option>");
    print("<option value='#30CC30' style='BACKGROUND-COLOR:#30CC30'>Green</option>");
    print("<option value='#00FF80' style='BACKGROUND-COLOR:#00FF80'>Light Green</option>");
    print("<option value='#98FC98' style='BACKGROUND-COLOR:#98FC98'>Light Lime</option>");
    print("<option value='#98CC30' style='BACKGROUND-COLOR:#98CC30'>Dark Lime</option>");
    print("<option value='#40E0D0' style='BACKGROUND-COLOR:#40E0D0'>Turquois</option>");
    print("<option value='#20B4A8' style='BACKGROUND-COLOR:#20B4A8'>Aquarium</option></select>");
    
    // Style
    print("<select name='font' onChange='bbfont(this.value);' title='Style'>");
    print("<option value='0' name='font'>Style</option><option value='Arial' style='font-family: Arial;'>Arial</option>");
    print("<option value='Comic Sans MS' style='font-family: Comic Sans MS;'>Comic</option><option value='Trebuchet MS' style='font-family: Trebuchet MS;'>Trebuchet</option>");
    print("<option value='Courier New' style='font-family: Courier New;'>Courier</option><option value='Georgia' style='font-family: Georgia;'>Georgia</option>");
    print("<option value='Impact' style='font-family: Impact;'>Impact</option><option value='Lucida Sans Unicode' style='font-family: Lucida Sans Unicode;'>Lucida</option>");
    print("<option value='Microsoft Sans Serif' style='font-family: Microsoft Sans Serif;'>Microsoft</option>");
    print("<option value='Tahoma' style='font-family:Tahoma;'>Tahoma</option><option value='Times New Roman' style='font-family:Times New Roman;'>Roman</option>");
    print("<option value='Verdana' style='font-family:Verdana;'>Verdana</option><option value='Palatino Linotype' style='font-family:Palatino Linotype;'>Palatino</option>");
    print("<option value='Ravie' style='font-family:Ravie;'>Ravie</option><option value='WESTERN' style='font-family:WESTERN;'>Western</option>");
    print("<option value='Amerika' style='font-family:Amerika;'>Amerika</option><option value='Goudy Old Style' style='font-family:Goudy Old Style;'>Goudy</option>");
    print("<option value='Papyrus' style='font-family: Papyrus;'>Papyrus</option><option value='Brush Script MT' style='font-family:Brush Script MT;'>Brush</option></select>");
    
    // Size
    print("<select name='size' onchange='bbsize(this.value);' title='Size'><option value='0' name='size'>Size &nbsp;&nbsp;</option>");
    print("<option value='1'>1x</option><option value='2'>2x</option><option value='3'>3x</option><option value='4'>4x</option>");
    print("<option value='5'>5x</option><option value='6'>6x</option><option value='7'>7x</option></select>"); ?>

    </div>
    </div>

    <div class="container">
    <div class="row justify-content-md-center">
    <div class="collapse" id="collapseExample">
    <div class="text-center"><?php
        
        // Smiliews/emoji
        global $emoji;
        foreach($emoji as $code => $url) { ?>
            <a title="<?= $url ?>" onclick="SmileIT('<?= $code ?>','<?= $form ?>','<?= $name ?>')"><?= $code ?></a>
            <?php
        } ?>

    </div>
    </div>
    </div>
    </div>

	<div class="row justify-content-md-center">
    <div class="col-10" style="width:100%;">
	    <textarea class="form-control" id="<?= $name ?>" name="<?= $name ?>" rows="13"><?= $content ?></textarea>
    </div>
	</div>

	<div class="row justify-content-md-center">
    <div class="col-10"> <?php

        // Refresh And Preview Button
        print("<center><input type='reset' class='btn btn-sm ttbtn' value='Refresh' />&nbsp;<input type='button' class='btn btn-sm ttbtn' value='Preview' onClick='visualisation()' /></center><br>");
        
        // Creation of the Preview Area
        print("<div id='previsualisation' width='200px' height='200px'></div><br>");
    ?>
	</div>
    </div>
<?php
}

function shoutbbcode($form, $name, $content = "")
{
    require "assets/js/BBTag.js"; ?>
    
    <div class="container">
    <div class="row justify-content-md-center">
    <div class="col-12 d-flex justify-content-center"> <?php

    // BBcode
    print("<i class='p-1 fa fa-bold' id='BBCode' name='Bold' style='height:20px; width:20px;' onclick=\"bbcomment('[b]', '[/b]')\" title='Bold' /></i>");
    print("<i class='p-1 fa fa-italic' id='BBCode' name='Italic' style='height:20px; width:20px;' onclick=\"bbcomment('[i]', '[/i]')\" title='Italic' /></i>");
    print("<i class='p-1 fa fa-underline' id='BBCode' name='Highlight' style='height:20px; width:20px;' onclick=\"bbcomment('[u]', '[/u]')\" title='Highlight'/></i>");
    print("<i class='p-1 fa fa-strikethrough' id='BBCode' name='Strike' style='height:20px; width:20px;' onclick=\"bbcomment('[s]', '[/s]')\" title='Strike' /></i>");
    print("<i class='p-1 fa fa-list' id='BBCode' name='List' style='height:20px; width:20px;' onclick=\"bbcomment('[list]', '[/list]')\" title='List'	/></i>");
    print("<i class='p-1 fa fa-quote-right' id='BBCode' name='Quote' style='height:20px; width:20px;'	onclick=\"bbcomment('[quote]', '[/quote]')\" title='Quote'	/></i>");
    print("<i class='p-1 fa fa-code' id='BBCode' name='Code'  style='height:20px; width:20px;' onclick=\"bbcomment('[code]', '[/code]')\" title='Code' /></i>");
    print("<i class='p-1 fa fa-link' id='BBCode' name='Url' style='height:20px; width:20px;' onclick=\"bbcomment('[url]', '[/url]')\"	title='Link' /></i>");
    print("<i class='p-1 fa fa-picture-o' id='BBCode' name='Image' style='height:20px; width:20px;' onclick=\"bbcomment('[img]', '[/img]')\" title='Image' /></i>");
    print("<i class='p-1 fa fa-bolt' id='BBCode' name='scroller' style='height:20px; width:20px;'	onclick=\"bbcomment('[df]', '[/df]')\" title='scroller' /></i>");
    print("<i class='p-1 fa fa-arrow-left' id='BBCode' name='Align Leftt' style='height:20px; width:20px;' onclick=\"bbcomment('[align=left]','[/align]')\" title='Align Left' /></i>");
    print("<i class='p-1 fa fa-align-center' id='BBCode' name='Align Center' style='height:20px; width:20px;' onclick=\"bbcomment('[align=center]','[/align]')\" title='Align Center' /></i>");
    print("<i class='p-1 fa fa-arrow-right' id='BBCode' name='Align Right' style='height:20px; width:20px;'	onclick=\"bbcomment('[align=right]','[/align]')\" title='Align Right' /></i>");
    print("<a href='https://imgur.com/upload' target='_blank' class='p-1' style=\"background: url('" . Url('assets/images/misc/bbcode_imgur.webp') . "');  height:20px; width:20px;\" title='Upload Image' /></a>");
    print("<a href='http://www.youtube.com'	target='_blank' class='p-1'	style=\"background: url('" . Url('assets/images/misc/bbcode_youtube.webp') . "');  height:20px; width:20px;\" title='YouTube' /></a>");
    
    // Smilies
    print("<a data-bs-toggle='collapse' data-bs-target='#collapseExample' aria-expanded='false' aria-controls='collapseExample'><img <img style='margin-bottom:16px;height:20px; width:20px;' src='" . Url('assets/images/smilies/grin.webp') . "' alt='' /></a>&nbsp;&nbsp;"); 
    
    // History & Staff
    echo "<a href=" . Url('shoutbox/history') . "><small><b>" . LANG["HISTORY"] . "</b></small></a>&nbsp;&nbsp;";

    // Choose the colour
    print("<select name='color' style='padding-bottom:3px;height:24px; width:100px;' onChange='bbcouleur(this.value);' title='Colour'>");
    print("<option value='0' name='color'>Colour</option>");
    print("<option value='#000000' style='BACKGROUND-COLOR:#000000'>Black</option>");
    print("<option value='#686868' style='BACKGROUND-COLOR:#686868'>Grey</option>");
    print("<option value='#708090' style='BACKGROUND-COLOR:#708090'>Dark Grey</option>");
    print("<option value='#C0C0C0' style='BACKGROUND-COLOR:#C0C0C0'>Light Grey</option>");
    print("<option value='#FFFFFF' style='BACKGROUND-COLOR:#FFFFFF'>White</option>");
    print("<option value='#FFFFE0' style='BACKGROUND-COLOR:#FFFFE0'>Beech</option>");
    print("<option value='#880000' style='BACKGROUND-COLOR:#880000'>Dark Red</option>");
    print("<option value='#B82428' style='BACKGROUND-COLOR:#B82428'>Light Red</option>");
    print("<option value='#FF0000' style='BACKGROUND-COLOR:#FF0000'>Red</option>");
    print("<option value='#FF1490' style='BACKGROUND-COLOR:#FF1490'>Dark Pink</option>");
    print("<option value='#FF68B0' style='BACKGROUND-COLOR:#FF68B0'>Pink</option>");
    print("<option value='#FFC0C8' style='BACKGROUND-COLOR:#FFC0C8'>Light Pink</option>");
    print("<option value='#FF4400' style='BACKGROUND-COLOR:#FF4400'>Dark Orange</option>");
    print("<option value='#FF6448' style='BACKGROUND-COLOR:#FF6448'>Redish Orange</option>");
    print("<option value='#FFA800' style='BACKGROUND-COLOR:#FFA800'>Orange</option>");
    print("<option value='#FFD800' style='BACKGROUND-COLOR:#FFD800'>Dark Yellow</option>");
    print("<option value='#cfbc38' style='BACKGROUND-COLOR:#cfbc38'>Yellow</option>");
    print("<option value='#FF00FF' style='BACKGROUND-COLOR:#FF00FF'>Light Purple</option>");
    print("<option value='#C01480' style='BACKGROUND-COLOR:#C01480'>Dark Purple</option>");
    print("<option value='#B854D8' style='BACKGROUND-COLOR:#B854D8'>Dark Violet</option>");
    print("<option value='#D8A0D8' style='BACKGROUND-COLOR:#D8A0D8'>Light Violet</option>");
    print("<option value='#000080' style='BACKGROUND-COLOR:#000080'>Darkest Blue</option>");
    print("<option value='#0000FF' style='BACKGROUND-COLOR:#0000FF'>Dark Blue</option>");
    print("<option value='#2090FF' style='BACKGROUND-COLOR:#2090FF'>Ble</option>");
    print("<option value='#00BCFF' style='BACKGROUND-COLOR:#00BCFF'>Light Blue</option>");
    print("<option value='#B0E0E8' style='BACKGROUND-COLOR:#B0E0E8'>Faint Blue</option>");
    print("<option value='#A02828' style='BACKGROUND-COLOR:#A02828'>Brown</option>");
    print("<option value='#F0A460' style='BACKGROUND-COLOR:#F0A460'>Brown Creme</option>");
    print("<option value='#D0B488' style='BACKGROUND-COLOR:#D0B488'>Light Brown</option>");
    print("<option value='#B8B868' style='BACKGROUND-COLOR:#B8B868'>Brown/Green</option>");
    print("<option value='#008000' style='BACKGROUND-COLOR:#008000'>Dark Green</option>");
    print("<option value='#30CC30' style='BACKGROUND-COLOR:#30CC30'>Green</option>");
    print("<option value='#00FF80' style='BACKGROUND-COLOR:#00FF80'>Light Green</option>");
    print("<option value='#98FC98' style='BACKGROUND-COLOR:#98FC98'>Light Lime</option>");
    print("<option value='#98CC30' style='BACKGROUND-COLOR:#98CC30'>Dark Lime</option>");
    print("<option value='#40E0D0' style='BACKGROUND-COLOR:#40E0D0'>Turquois</option>");
    print("<option value='#20B4A8' style='BACKGROUND-COLOR:#20B4A8'>Aquarium</option></select>");
    ?>
    </div>

    <div class="container">
    <div class="row justify-content-md-center">
    <div class="collapse" id="collapseExample">
    <div class="text-center"> <?php

        // Smiliews/emoji
        global $emoji;
        foreach($emoji as $code => $url) {
            ?>
            <a title="<?= $url ?>" onclick="SmileIT('<?= $code ?>','<?= $form ?>','<?= $name ?>')"><?= $code ?></a>
            <?php
        } ?>

    </div>
    </div>
    </div>
    </div>

    <div class="col-md-11">
        <input id="message" class="form-control" type='text' size='100%' name="<?= $name ?>"><?= $content ?>
    </div>
    <div class="col-md-1">
        <center><input type='submit' name='submit' value='<?= LANG["SUBMIT"] ?>' class='btn btn-sm ttbtn' /></center>
    </div>

    </div>
    </div> <?php
}

// Time Zones Array Php DateTimeZone (Default Used is English !!!)
$tzs = array();
$tzs["-720"] = array("(GMT - 12:00 hours) Enitwetok, Kwajalien", "Pacific/Kwajalein");
$tzs["-660"] = array("(GMT - 11:00 hours) Midway Island, Samoa", "Pacific/Samoa");
$tzs["-600"] = array("(GMT - 10:00 hours) Hawaii", "Etc/GMT+10");
$tzs["-540"] = array("(GMT - 9:00 hours) Alaska", "America/Anchorage");
$tzs["-480"] = array("(GMT - 8:00 hours) Pacific Time (US &amp; Canada)", "America/Los_Angeles");
$tzs["-420"] = array("(GMT - 7:00 hours) Mountain Time (US &amp; Canada)", "America/Denver");
$tzs["-360"] = array("(GMT - 6:00 hours) Central Time (US &amp; Canada), Mexico City", "America/Chicago");
$tzs["-300"] = array("(GMT - 5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito", "America/New_York");
$tzs["-240"] = array("(GMT - 4:00 hours) Atlantic Time (Canada), Caracas, La Paz", "America/Guadeloupe");
$tzs["-210"] = array("(GMT - 3:30 hours) Newfoundland", "America/St_Johns");
$tzs["-180"] = array("(GMT - 3:00 hours) Brazil, Buenos Aires, Georgetown, Falkland Isl.", "Etc/GMT+3");
$tzs["-120"] = array("(GMT - 2:00 hours) Mid-Atlantic, Ascention Isl., St Helena", "Atlantic/St_Helena");
$tzs["-60"] = array("(GMT - 1:00 hour) Azores, Cape Verde Islands", "Atlantic/Azores");
$tzs["0"] = array("(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia", "Europe/London");
$tzs["60"] = array("(GMT + 1:00 hour) Amsterdam, Berlin, Copenhagen, Madrid, Paris, Rome", "Europe/Rome");
$tzs["120"] = array("(GMT + 2:00 hours) Kaliningrad, South Africa, Warsaw", "Europe/Kaliningrad");
$tzs["180"] = array("(GMT + 3:00 hours) Baghdad, Riyadh, Moscow, Nairobi", "Africa/Nairobi");
$tzs["210"] = array("(GMT + 3:30 hours) Tehran", "Asia/Tehran");
$tzs["240"] = array("(GMT + 4:00 hours) Adu Dhabi, Baku, Muscat, Tbilisi", "Asia/Tbilisi");
$tzs["270"] = array("(GMT + 4:30 hours) Kabul", "Asia/Kabul");
$tzs["300"] = array("(GMT + 5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent", "Asia/Tashkent");
$tzs["330"] = array("(GMT + 5:30 hours) Bombay, Calcutta, Madras, New Delhi", "Asia/Calcutta");
$tzs["360"] = array("(GMT + 6:00 hours) Almaty, Dhaka, Khulna", "Asia/Dhaka");
$tzs["420"] = array("(GMT + 7:00 hours) Bangkok, Hanoi, Jakarta", "Asia/Bangkok");
$tzs["480"] = array("(GMT + 8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei", "Asia/Taipei");
$tzs["540"] = array("(GMT + 9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk", "Asia/Tokyo");
$tzs["570"] = array("(GMT + 9:30 hours) Adelaide, Darwin", "Australia/Adelaide");
$tzs["600"] = array("(GMT + 10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok", "Australia/Sydney");
$tzs["660"] = array("(GMT + 11:00 hours) Magadan, New Caledonia, Solomon Islands", "Asia/Magadan");
$tzs["720"] = array("(GMT + 12:00 hours) Auckland, Wellington, Fiji, Marshall Island", "Pacific/Auckland");

// Get Date By User Timezone 
function user_timezone_date($timestamp, $return_int = false)
{
	if (!is_numeric($timestamp)) {
	    $timestamp = strtotime($timestamp); // date to time
	}

	$timestamp = $timestamp + ($_SESSION['tzoffset']*60); // do time adjust
    /*
	if (date("I")) {
	    $timestamp += 3600; // DST Fix
	}
    */
	if ($return_int) {
	    return $timestamp;
	}
	
	return date("Y-m-d H:i:s", $timestamp);

}

// Drop Down for Time Zones
function timeZoneDropDown($tzoffset)
{
    global $tzs;
    $tz = '';
    ksort($tzs);
    reset($tzs);
    foreach ($tzs as $key => $val) {
        if ($tzoffset == $key) {
            $tz .= "<option value=\"$key\" selected='selected'>$val[0]</option>\n";
        } else {
            $tz .= "<option value=\"$key\">$val[0]</option>\n";
        }
    }
    return $tz;
}

// $type=date/time || $target=item || $amount=add/sub date
function modify($type, $target, $amount)
{
    if ($type == 'date') {
        $date = date_create($target);
        date_modify($date, $amount);
    } elseif ($type == 'time') {
        $date = date_create();
        date_timestamp_set($date, $target);
        date_modify($date, $amount);
    }

    return date_format($date, 'Y-m-d H:i:s');
}

// Obtain Week,Day,Hour,Minute Second From Time (INT)
function get_elapsed_time($ts)
{
    $mins = floor((CONFIG['TT_TIME'] - $ts) / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    if ($weeks > 0) {
        return "$weeks wk" . ($weeks > 1 ? "s" : "");
    }
    if ($days > 0) {
        return "$days day" . ($days > 1 ? "s" : "");
    }
    if ($hours > 0) {
        return "$hours hr" . ($hours > 1 ? "s" : "");
    }
    if ($mins > 0) {
        return "$mins min" . ($mins > 1 ? "s" : "");
    }
    return "< 1 min";
}

// Function For Comment Table
function commenttable($res, $type = '')
{
    while ($row = $res->fetch(PDO::FETCH_LAZY)) {
        $postername = coloredname($row["user"]);
        if ($postername == "System") {
            $title = "";
            $avatar = "";
            $usersignature = "";
            $userdownloaded = "";
            $useruploaded = "";
        } else {
            $privacylevel = $row["privacy"];
            $avatar = htmlspecialchars($row["avatar"]);
            $title = format_comment($row["title"]);
            $usersignature = stripslashes(format_comment($row["signature"]));
            $userdownloaded = mksize($row["downloaded"]);
            $useruploaded = mksize($row["uploaded"]);
        }

        $userratio = $row["downloaded"] > 0 ? number_format($row["uploaded"] / $row["downloaded"]) : "---";

        if (!$avatar) {
            $avatar = URLROOT . "/assets/images/misc/default_avatar.webp";
        }

        $commenttext = format_comment($row["text"]);

        $edit = null;
        if ($type == "torrent" && $_SESSION['edit_torrents'] == 'yes' || $type == "news"  || $type == "req" && $_SESSION['edit_news'] == 'yes' || $_SESSION['id'] == $row['user'] && $_SESSION['id']) {
            $edit = '<a href="#" data-bs-toggle="modal" data-bs-target="#editModal'.$row['id'].'"><i class="fa fa-pencil" title="'. LANG["EDIT"] .'"></i></a>';
        }

        $delete = null;
        if ($type == "torrent" && $_SESSION['delete_torrents'] == 'yes' || $type == "req" || $type == "id" || $type == "news" && $_SESSION['delete_news'] == 'yes') {
            $delete = '<a href=' . URLROOT . '/comment/delete/' . $row['id'] . '><i class="fa fa-trash" title="'. LANG["DEL"] .'"></i></a>';
        }

        ?>
        <div class='row p-4'>
            <div class="col-2">
                <?php if (!$_SESSION['edit_users'] == 'yes' && $privacylevel == 'strong') : ?>
                    <a href="<?= URLROOT ?>/profile/<?= $row['id'] ?>"><b><?= $postername ?></b></a>
                    <?= $title ?><br>
                    <img width="80" height="80" src="<?= $avatar ?>" alt="" /><br>
                    <i class="fa fa-upload" title="upload"></i> <?= $useruploaded ?><br />
                    <i class="fa fa-download" title="download"></i> <?= $userdownloaded ?><br />
                    <?= LANG["RATIO"] ?>: <?= get_ratio_color($userratio) ?><br />
                <?php else : ?>
                    <a href="<?= URLROOT ?>/profile/<?= $row['id'] ?>"><b><?= LANG['PRIVATE'] ?></b></a>
                    <?= $title ?><br>
                    <img width="80" height="80" src="<?= URLROOT ?>/assets/images/misc/default_avatar.webp" alt="" /><br>
                    <i class="fa fa-upload" title="upload"></i> <?= $useruploaded ?><br />
                    <i class="fa fa-download" title="download"></i> <?= $userdownloaded ?><br />
                    <?= LANG["RATIO"] ?>: <?= get_ratio_color($userratio) ?><br />
                <?php endif; ?>
            </div>
            <div class="col-10">
                <?= LANG["POSTED"] ?>: <?php echo  user_timezone_date($row["added"]) ?> <a id="comment<?= $row["id"] ?>"></a><br>
                <div class="p-2"><?= $commenttext ?></div>
                <hr>
                <?= $usersignature ?>
            </div>
            <div class="col-12">
                <?= $edit ?> <?= $delete ?> <a href="#" data-bs-toggle="modal" data-bs-target="#reportModal"><i class="fa fa-flag" title="<?= LANG["REPORT"] ?>"></i></a>
            </div>
        </div>

        <!-- Edit Modal -->
        <form id="submit" method="post" action="<?= URLROOT ?>/comment/submit/<?= $row['id'] ?>/<?= $type ?>">
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <input type='hidden' name="comment_id" value='<?= $row["id"] ?>' />
                            <?php print textbbcode("submit", "text", $row["text"]); ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= LANG["CLOSE"] ?></button>
                            <button type="submit" class="btn btn-sm ttbtn"><?= LANG["SAVE"] ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Report Modal -->
        <form id="report" method="post" action="<?= Url('report') ?>">
            <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md" style="max-width:700px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="text-center"><?= LANG["ARE_YOU_SURE_REPORT"] ?> comment.</p>
                            <input type='hidden' name='id' value='<?= $row["id"] ?>' />
                            <input type='hidden' name='postid' value='<?= $row["text"] ?>' />
                            <input type='hidden' name='type' value='<?= $type ?>' />
                            <p class="text-center"><?= LANG["REASON"] ?>.</p>
                            <input class="form-control fullwidth" type='text' name='reason' /><br>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm ttbtn" data-bs-dismiss="modal"><?= LANG["CLOSE"] ?></button>
                            <button type="submit" class="btn btn-sm ttbtn"><?= LANG["SAVE"] ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php
    }
}

// Function To Display Tables Of Torrents
function torrenttable($res, $columns = CONFIG['TORRENTTABLE_COLUMNS'])
{
    // Set Waiting Time
    if (CONFIG['MEMBERSONLY_WAIT'] && CONFIG['MEMBERSONLY'] && in_array($_SESSION['class'], explode(",", CONFIG['CLASS_WAIT']))) {
        $gigs = $_SESSION['uploaded'] / (1024 * 1024 * 1024);
        $userratio = $_SESSION['downloaded'] > 0 ? number_format($_SESSION['uploaded'] / $_SESSION['downloaded'], 1) : "---";
        if ($userratio < 0 || $gigs < 0) {
            $wait = CONFIG['A_WAIT'];
        } elseif ($userratio < CONFIG['RATIOA'] || $gigs < CONFIG['GIGSA']) {
            $wait = CONFIG['A_WAIT'];
        } elseif ($userratio < CONFIG['RATIOB'] || $gigs < CONFIG['GIGSB']) {
            $wait = CONFIG['B_WAIT'];
        } elseif ($userratio < CONFIG['RATIOC'] || $gigs < CONFIG['GIGSC']) {
            $wait = CONFIG['C_WAIT'];
        } elseif ($userratio < CONFIG['RATIOD'] || $gigs < CONFIG['GIGSD']) {
            $wait = CONFIG['D_WAIT'];
        } else {
            $wait = 0;
        }
    }
    $wait = '';
    // Columns
    $cols = explode(",", $columns);
    $cols = array_map("strtolower", $cols);
    $cols = array_map("trim", $cols);
    $colspan = count($cols);
    // Start The Table
    echo '<div class="table-responsive"><table class="table table-striped"><thead><tr>';

    foreach ($cols as $col) {
        switch ($col) {
            case 'category':
                echo "<th>" . LANG["TYPE"] . "</th>";
                break;
            case 'name':
                echo "<th>" . LANG["NAME"] . "</th>";
                break;
            case 'dl':
                echo "<th>" . LANG["DL"] . "</th>";
                break;
            case 'magnet':
                echo "<th>" . LANG["MAGNET"] . "</th>";
                break;
            case 'uploader':
                echo "<th>" . LANG["UPLOADER"] . "</th>";
                break;
            case 'tube':
                echo "<th>" . LANG["YOUTUBE"] . "</th>";
                break;
            case 'tmdb':
                echo "<th>" . LANG["TMDB"] . "</th>";
                break;
            case 'comments':
                echo "<th>" . LANG["COMM"] . "</th>";
                break;
            case 'nfo':
                echo "<th>" . LANG["NFO"] . "</th>";
                break;
            case 'size':
                echo "<th>" . LANG["SIZE"] . "</th>";
                break;
            case 'completed':
                echo "<th>" . LANG["C"] . "</th>";
                break;
            case 'seeders':
                echo "<th>" . LANG["S"] . "</th>";
                break;
            case 'leechers':
                echo "<th>" . LANG["L"] . "</th>";
                break;
            case 'health':
                echo "<th>" . LANG["HEALTH"] . "</th>";
                break;
            case 'external':
                if (CONFIG['ALLOWEXTERNAL']) {
                    echo "<th>" . LANG["L/E"] . "</th>";
                }
                break;
            case 'added':
                echo "<th>" . LANG["ADDED"] . "</th>";
                break;
            case 'speed':
                echo "<th>" . LANG["SPEED"] . "</th>";
                break;
            case 'wait':
                if ($wait) {
                    echo "<th>" . LANG["WAIT"] . "</th>";
                }
                break;
            case 'rating':
                echo "<th>" . LANG["RATINGS"] . "</th>";
                break;
            case 'admin':
                if ($_SESSION['class'] == 7) {
                    echo "<th>" . LANG["STAFF"] . "</th>";
                }
                break;
        }
    }
    // Do They Have To Wiait
    if ($wait && !in_array("wait", $cols)) {
        echo "<th>" . LANG["WAIT"] . "</th>";
    }

    echo "</tr></thead>";

    foreach ($res as $row) {
        $id = $row["id"];
        print("<tr>\n");

        foreach ($cols as $col) {
            switch ($col) {
                case 'category':
                    print("<td>");
                    if (!empty($row["cat_name"])) {
                        print("<center><a href=\"" . URLROOT . "/browse?cat=" . $row["category"] . "\">");
                        if (!empty($row["cat_pic"]) && $row["cat_pic"] != "") {
                            print("<i class='fa ".$row['cat_pic']."' style='font-size:22px;' title='".$row["cat_name"]."'>  </i>");
                        } else {
                            print($row["cat_parent"] . ": " . $row["cat_name"]);
                        }
                        print("</a></center>");
                    } else {
                        print("-");
                    }
                    print("</td>\n");
                    break;
                case 'name':
                    $char1 = 40; //cut name length
                    $smallname = htmlspecialchars(CutName($row["name"], $char1));
                    $dispname = "<b>" . $smallname . "</b>";
                    $added = user_timezone_date($row['added']);
                    $type ='';
                    if (strtotime($added) >= strtotime("yesterday")){ // Only show for torrents 1 day old
                        $type .= "<b><font color='#ff0000'> (" . LANG["NEW"] . "!) </font></b>";
                    }
                    if ($row["freeleech"] == 1) {
                        $type .= " <i class='fa fa-sun-o' title='" . LANG["FREELEECH"] . "'></i>";
                    }
                    if ($row["vip"] == "yes") {
                        $type .= " <i class='fa fa-user-secret' title='" . LANG["VIP"] . "'></i>";
                    }
                    if ($row["sticky"] == "yes") {
                        $type .= " <i class='fa fa-thumb-tack' title='" . LANG["STICKY"] . "'></i>";
                    }
                    //print("<td class='ttable_col$x' nowrap='nowrap'><a href=\"" . URLROOT . "/torrent/$id/hit\">$dispname</a></td>");
                    // BALLOON TOOLTIP MOD
                    print("<td nowrap='nowrap'><a href=\"" . URLROOT . "/torrent/$id/hit\" onMouseover=\"return overlib('<table class=ballooncolor border=1 width=300px align=center><tr><td class=balloonheadercolor colspan=2 align=center>$smallname</td></tr><tr valign=top><td class=ballooncolor align=center><img border=0 height=200 width=120 src=".TmdbLogic::getImage($row)."></td><td width=80%  class=ballooncolor><div align=left><b>" . LANG["UPLOADED"] . ": </b>" . user_timezone_date($row["added"]) . "<br /><b>" . LANG["SIZE"] . ": </b>". mksize($row["size"]) . "<br /><b>" . LANG["COMPLETED"] . ": </b>" . $row["times_completed"] . "<br /></div><div align=left><b>Views: </b>" . $row["views"] . "<br /><b>" . LANG["HITS"] . ": </b>" . $row["hits"] . "<br /><b>" . LANG["SEEDERS"] . ": </b><font color=green>" . $row["seeders"] . "</font><br /><b>" . LANG["LEECHERS"] . ": </b><font color=red>" . $row["leechers"] . "</font><br /><b>" . LANG["BY"] . ": </b>" . $row['username'] . "</div></td></tr><tr><td class=balloonheadercolor colspan=2 align=center>".LANG["DESCRIPTION"]."</td></tr><tr><td  class=ballooncolor colspan=2>" . $row['descr'] . "</td></tr></table>', CENTER, HEIGHT, 200, WIDTH, 300)\"; onMouseout=\"return nd()\">".$dispname."</a>".$type."");

                    $tags = get_tags($id);
                    print($tags."</td>");
                    
                    break;
                case 'dl':
                    print("<td><a href=\"" . URLROOT . "/download/$id\"><i class='fa fa-download' style='font-size:22px;color:green' title='" . LANG["DOWNLOADED"] . "'></i></a></td>");
                    break;
                case 'magnet':
                    // Like Mod
                    if (!CONFIG['FORCETHANKS'] && $row['external'] == 'yes') {
                        print("<td><a href=\"magnet:?xt=urn:btih:" . $row["info_hash"] . "&dn=" . rawurlencode($row['name']) . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-2x' style='font-size:23px;color:green' title='" . LANG["DOWNLOADED"] . "'></i></a></td>");
                    } elseif (CONFIG['FORCETHANKS']) {
                        $likes_query = "SELECT user FROM `likes` WHERE liked =? AND type =? AND user =?";
                        $like = DB::run($likes_query, [$id, 'torrent', $_SESSION['id']])->fetch(PDO::FETCH_ASSOC);
                        if ($like) {
                            if ($_SESSION['can_download'] == 'yes' && $row['external'] == 'yes') {
                                print("<td><a href=\"magnet:?xt=urn:btih:" . $row["info_hash"] . "&dn=" . rawurlencode($row['name']) . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-2x' style='font-size:23px;color:green' title='" . LANG["DOWNLOADED"] . "'></i></a></td>");
                            } else {
                                print("<td><h8><span class='badge border border-1 border-danger'>No Dload</span></h8></td>");
                            }
                        } elseif ($_SESSION['id'] == $row["owner"] && $row['external'] == 'yes') {
                            print("<td><a href=\"magnet:?xt=urn:btih:" . $row["info_hash"] . "&dn=" . rawurlencode($row['name']) . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-2x' style='font-size:23px;color:green' title='" . LANG["DOWNLOADED"] . "'></i></a></td>");
                        } else {
                            print("<td><a href='" . URLROOT . "/like/thanks?id=$id&type=torrent><h8><span class='badge border border-1 border-danger'>" . LANG["THANKS"] . "</span></h8></td>");
                        }
                    } else {
                        print("<td><i class='fa fa-home' style='font-size:23px;color:green' title='" . LANG["LOCAL"] . "'></i></td>");
                    }
                    break;
                case 'uploader':
                    echo "<td>";
                    if (($row["anon"] == "yes" || $row["privacy"] == "strong") && $_SESSION['id'] != $row["owner"] && !$_SESSION['edit_torrents'] == 'yes') {
                        echo "Anonymous";
                    } elseif ($row["username"]) {
                        echo "<a href='" . URLROOT . "/profile/$row[owner]'>" . coloredname($row['owner']) . "</a>";
                    } else {
                        echo "Unknown";
                    }

                    echo "</td>";
                    break;
                case 'tube':
                    if ($row["tube"]) {
                        print("<td><a rel=\"prettyPhoto\"  href=" . $row['tube'] . " ><" . htmlspecialchars($row['tube']) . "><i class='fa fa-youtube-play' style='font-size:32px;color:#ff2c2c' title='" . LANG["YOUTUBE"] . "'></i></a></td>");
                    } else {
                        print("<td>-</td>");
                    }
                    break;
                case 'tmdb':
                    if ($row["imdb"]) {
                        print("<td><a href=" . $row['imdb'] . "><img src='" . URLROOT . "/assets/images/misc/imdb.webp'  border='0' width='28' height='25' alt=\"\" /></a></td>");
                    } elseif ($row["tmdb"]) {
                        print("<td><a href=" . $row['tmdb'] . "><img src='" . URLROOT . "/assets/images/misc/tmdb1.webp'  border='0' width='28' height='25' alt=\"\" /></a></td>");
                    } else {
                        print("<td>-</td>");
                    }
                    break;
                case 'comments':
                    print("<td><a href='" . URLROOT . "/comment/$id/torrent'><b>$row[comments]</b></a></td>\n");
                    break;
                case 'nfo':
                    if ($row["nfo"] == "yes") {
                        print("<td><i class='fa fa-file-text-o' title='" . LANG["NFO"] . "'></i></a></td>");
                    } else {
                        print("<td>-</td>");
                    }
                    break;
                case 'size':
                    print("<td style='min-width:110px;'>" . mksize($row["size"]) . "</td>");
                    break;
                case 'completed':
                    print("<td><font color='orange'><b>" . number_format($row["times_completed"]) . "</b></font></td>");
                    break;
                case 'seeders':
                    print("<td><font color='green'><b>" . number_format($row["seeders"]) . "</b></font></td>\n");
                    break;
                case 'leechers':
                    print("<td><font color='#ff0000'><b>" . number_format($row["leechers"]) . "</b></font></td>\n");
                    break;
                case 'health':
                    print("<td><img src='" . URLROOT . "/assets/images/health/health_" . health($row["leechers"], $row["seeders"]) . ".webp' alt='' /></td>\n");
                    break;
                case 'external':
                    if (CONFIG['ALLOWEXTERNAL']) {
                        if ($row["external"] == 'yes') {
                            print("<td>" . LANG["E"] . "</td>\n");
                        } else {
                            print("<td>" . LANG["L"] . "</td>\n");
                        }
                    }
                    break;
                case 'added':
                    print("<td style='min-width:110px;'>" . get_elapsed_time(user_timezone_date($row["added"], true)) . "</td>");
                    break;
                case 'speed':
                    if ($row["external"] != "yes" && $row["leechers"] >= 1) {
                        $peers_query = "SELECT (SUM(downloaded)) / (UNIX_TIMESTAMP(?) - UNIX_TIMESTAMP(started)) AS totalspeed FROM `peers` 
                        WHERE seeder =? AND torrent=? ORDER BY started ASC";
                        $speedQ =DB::run($peers_query, [CONFIG['TT_DATE'], 'no',$id])->fetchColumn();
                        $totalspeed = mksize($speedQ) . "/s";
                    } else {
                        $totalspeed = "--";
                    }
                    print("<td>$totalspeed</td>");
                    break;
                case 'wait':
                    if ($wait) {
                        $elapsed = floor((CONFIG['TT_TIME'] - strtotime($row["added"])) / 3600);
                        if ($elapsed < $wait && $row["external"] != "yes") {
                            $color = dechex(floor(127 * ($wait - $elapsed) / 48 + 128) * 65536);
                            print("<td><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></td>\n");
                        } else {
                            print("<td>--</td>\n");
                        }

                    }
                    break;
                case 'rating':
                    if (!$row["rating"]) {
                        $rating = "--";
                    } else {
                        $average = ceil($row["rating"]);
                        $rating = "<img src=\"" . URLROOT . "/assets/images/rating/$average.webp\" border=\"0\" alt=\"rating: $average/5\" title=\"rating: $average/5\" /></a>";
                    }
                    //$rating = $row["rating"] . "/5)";
                    print("<td>$rating</td>");
                    break;
                case 'admin':
                    if ($_SESSION['class'] == 7) {
                        print("<td><input type='checkbox' name='ids[]' value='".$row["id"]."' /></td>");
                    }
                    break;
            }
            
        }

        // Wait Time Check
        if ($wait && !in_array("wait", $cols)) {
            $elapsed = floor((CONFIG['TT_TIME'] - strtotime($row["added"])) / 3600);
            if ($elapsed < $wait && $row["external"] != "yes") {
                $color = dechex(floor(127 * ($wait - $elapsed) / 48 + 128) * 65536);
                print("<td><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></td>\n");
            } else {
                print("<td>--</td>\n");
            }
            $colspan++;
        }

        print("</tr>\n");
    }

    print("</table></div><br />\n");
}

   
function topcountry($data)
{
$html = "<div class='table-responsive'><table class='table table-striped'>";
$html .= "<thead><tr>";
$html .= "<th>Rank</th>";
$html .= "<th>Country&nbsp;Flag</th>";
$html .= " <th>Country&nbsp;Name</th>";
$html .= "</tr></thead>";

$num = 0;
foreach ($data as $a) {
    if ($a['flagpic']) {
        $flag = "<img align=center src=".Url("assets/images/languages/$a[flagpic]").">";
    } else {
        $flag = "<img align=center src=".Url('assets/images/languages/unknown.webp').">";
    }
    if ($a['name']) {
        $name = "<b>$a[name]</b>";
    } else {
        $name = "<b>Land of Homeless!</b>";
    }
    $html .= "<tbody><tr>";
    $html .= "<td align=center class=table_col1>$num</td>";
    $html .= "<td align=center class=table_col2>$flag</td>";
    $html .= "<td class=table_col1>$name</td>";
    $html .= "</tr></tbody>";
}
$html .= "</table></div>";
return $html;
}
    
function toptorrent($data)
{
         $html = "<div class='table-responsive'><table class='table table-striped'>";
$html .= "<thead><tr>";
$html .= "<th>Rank</th>";
$html .= "<th>Name</th>";
$html .= "<th><i class='fa fa-check tticon' title='Completed'></i></th>";
$html .= "<th>Traffic</th>";
$html .= "<th><i class='fa fa-upload tticon' title='". LANG["SEEDING"] ."'></th>";
$html .= "<th><i class='fa fa-downloadload tticon' title='". LANG["LEECHERS"] ."'></i></th>";
$html .= "<th>Total</th>";
$html .= "<th>Ratio</th>";
$html .= "</tr></thead>";

$num = 0;
foreach ($data as $a) {
    ++$num;
    $userratio = $a["leechers"] > 0 ? number_format($a["seeders"] / $a["leechers"], 1) : "---";
    $html .= "<tbody><tr>";
        $html .= "<td>$num</td>";
        $html .= "<td><a href=".Url("torrent/$a[id]/hit")."><b>" . $a["name"] . "</b></a></td>";
       $html .= " <td><font color=#0080FF><b>" . number_format($a["times_completed"]) . "</b></font></td>";
        $html .= "<td>" . mksize($a["data"]) . "</td>";
        $html .= "<td><font color=limegreen><b>" . number_format($a["seeders"]) . "</b></font></td>";
       $html .= " <td><font color=red><b>" . number_format($a["leechers"]) . "</b></font></td>";
        $html .= "<td>" . ($a["leechers"] + $a["seeders"]) . "</td>";
        $html .= "<td>".get_ratio_color($userratio)."</td>";
         $html .= "</tr><tbody>";
}
$html .= "</table></div>";
return $html;
}
    
function topuser($data)
{
         $html = "<div class='table-responsive'><table class='table table-striped'>";
$html .= "<thead><tr>";
$html .= "<th>Rank</td>";
$html .= "<th>User</td>";
$html .= "<th>Uploaded</td>";
$html .= " <th>UL speed</td>";
$html .= "<th>Downloaded</td>";
$html .= "<th>DL speed</td>";
$html .= "<th>Ratio</td>";
$html .= "<th>Joined</td>";
$html .= "</tr></thead>";
$num = 0;
foreach ($data as $a) {
    ++$num;
    $userratio = $a["downloaded"] > 0 ? number_format($a["uploaded"] / $a["downloaded"], 1) : "---";
    
    $html .= "<tbody><tr>";
        $html .= "<td>$num</td>";
        $html .= "<td><a href=".Url("profile/$a[userid]")."><b>" . coloredname($a["userid"]) . "</b>" . "</td>";
        $html .= "<td><font color=limegreen>" . mksize($a["uploaded"]) . "</font></td>";
        $html .= "<td><font color=limegreen>" . mksize($a["upspeed"]) . "/s" . "</font></td>";
        $html .= "<td><font color=#FF2400>" . mksize($a["downloaded"]) . "</font></td>";
        $html .= "<td><font color=#FF2400>" . mksize($a["downspeed"]) . "/s" . "</font></td>";
        $html .= "<td>" . get_ratio_color($userratio) . "</td>";
        $html .= "<td>" . date("d-M-Y", strtotime($a["added"])) . "</td>";
       $html .= " </tr><tbody>";
}
$html .= "</table></div>";
return $html;
}

// Function That Allows HTML Encoding
function encodehtml($s, $linebreaks = true)
{
    $s = str_replace("<", "&lt;", str_replace("&", "&amp;", $s));
    if ($linebreaks) {
        $s = nl2br($s);
    }
    return $s;
}

// Parse String (BBcode)
function format_comment($text)
{
    $s = $text;
    $s = htmlspecialchars($s);
    $s = preg_replace(
        "/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i",
        "\\1<a href='\\2'>\\2</a>", $s);
    // [*] [list]
    $s = preg_replace("/\[\*\]/", "<li>", $s);
    $s = preg_replace("#\[list\](.+)\[/list\]#isU", "<li style= 'margin-left: 20px;'>$1</li>", $s);
    // [center]
    $s = preg_replace("#\[center\](.+)\[/center\]#isU", "<center>$1</center>", $s);
    // [b]Bold[/b]
    $s = preg_replace("/\[b\]((\s|.)+?)\[\/b\]/", "<b>\\1</b>", $s);
    // [i]Italic[/i]
    $s = preg_replace("/\[i\]((\s|.)+?)\[\/i\]/", "<i>\\1</i>", $s);
    // [u]Underline[/u]
    $s = preg_replace("/\[u\]((\s|.)+?)\[\/u\]/", "<u>\\1</u>", $s);
    // [s]Line[/s]
    $s = preg_replace("#\[s\](.+)\[/s\]#isU", "<span style='text-decoration:line-through;'>$1</span>", $s);
    // [url]Links[/url]
    $s = preg_replace("#\[url\]((?:ftp|https?)://.*?)\[/url\]#i", "<a href='$1'>$1</a>", $s);
    $s = preg_replace("#\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]#i", "<a href='$1'>$2</a>", $s);
    // [img]Image[/img]
    $s = preg_replace("#\[img\]((?:ftp|https?)://[a-z0-9._/-]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\[/img\]#isU", "<img src='$1' class='scaleA'/>", $s);
    $s = preg_replace("#\[img=((?:ftp|https?)://[a-z0-9._/-]+(\.gif|\.jpg|\.png|\.bmp|\.jpeg))\]\[/img\]#isU", "<img src='$1' class='scaleA'/>", $s);
    // [Code]Code[/Code]
    $s = preg_replace("#\[code\](.+)\[/code\]#isU", "<b> Code : </b><pre><code><div class='scaleA' style='max-height:400px;white-space: nowrap;'  readonly='readonly'>$1</div></code></pre>", $s);
    // [Quote]Quote[/Quote]
    while (preg_match("#\[quote\](.+)\[/quote\]#isU", $s)) {
        $s = preg_replace("#\[quote\](.+)\[/quote\]#isU", "<blockquote><strong> Quote : </strong></blockquote><div class='scaleA' style='padding:5px; border: 1px black dotted'>$1</div><br />", $s);
    }
    while (preg_match("#\[quote=(.+)\](.+)\[/quote\]#isU", $s)) {
        $s = preg_replace("#\[quote=(.+)\](.+)\[/quote\]#isU", "<blockquote><strong>$1 Quote : </strong></blockquote><div class='scaleA' style='padding:5px; border: 1px black dotted'>$2</div><br />", $s);
    }
    // [video]
    $s = preg_replace("#\[video\](https://www.youtube.com.*v=.+)\[\/video\]#isU", "<center><embed src='https://www.youtube.com/v/$1' type='video' height='140'></embed></center>", $s);
    // [audio]
    $s = preg_replace("#\[audio\]((?:ftp|https?)://[a-z0-9._/-]+(\.mp3|\.wav|\.wma|\.aac|\.bwf|\.ogg|\.ac3|\.flac|\.asx|\.pls|\.alac))\[/audio\]#isU", "<img src='$1' />", $s);
    // [color=text]
    $s = preg_replace("#\[color=([a-zA-Z]+)\](.+)\[/color\]#isU", "<font color='$1'>$2</font>", $s);
    // [color=#hexcode]
    $s = preg_replace("#\[color=\#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})\](.+)\[/color\]#isU", "<font color='$1'>$2</font>", $s);
    // Size of Style
    $s = preg_replace("#\[size=([1-7])\](.+)\[/size\]#isU", "<font size='$1'>$2</font>", $s);
    // Type of Style
    $s = preg_replace("#\[font=([a-zA-Z].*?)\](.+)\[/font\]#isU", "<font face='$1'>$2</font>", $s);
    // Blink
    $s = preg_replace("#\[blink\](.+)\[/blink\]#isU", "<div id='blink'>$1</div>", $s);
    // Scroller
    $s = preg_replace("#\[df\](.+)\[/df\]#isU", "<div class='marquee'>$1</div>", $s);
    // [align=(center|left|right)]
    $s = preg_replace("/\[align=([a-zA-Z]+)\](.+?)\[\/align\]/is", "<div style=\"text-align:\\1\">\\2</div>", $s);
    // [hr]
    $s = preg_replace("/\[hr\]/i", "<hr />", $s);
    //[hr=#ffffff] [hr=red]
    $s = preg_replace("/\[hr=((#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])|([a-zA-z]+))\]/i", "<hr color=\"\\1\"/>", $s);
    // Linebreaks
    $s = nl2br($s);
    // Maintain spacing
    $s = str_replace("  ", " &nbsp;", $s);

    return $s;
}

// get image embeded image
function data_uri($file, $mime)
{
    $contents = file_get_contents($file);
    $base64 = base64_encode($contents);
    return ('data:' . $mime . ';base64,' . $base64);
}

// Returns The Size
function mksize($s, $precision = 2)
{
    $suf = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

    for ($i = 1, $x = 0; $i <= count($suf); $i++, $x++) {
        if ($s < pow(1024, $i) || $i == count($suf)) // Change 1024 to 1000 if you want 0.98GB instead of 1,0000MB
        {
            return number_format($s / pow(1024, $x), $precision) . " " . $suf[$x];
        }
    }
}

// Shorten Name
function CutName($vTxt, $Car)
{
    if (strlen($vTxt) > $Car) {
        return substr($vTxt, 0, $Car) . "...";
    }
    return $vTxt;
}

// Returns a numeric conversion according to a string
function strtobytes($str)
{
    $str = trim($str);
    if (!preg_match('!^([\d\.]+)\s*(\w\w)?$!', $str, $matches)) {
        return 0;
    }

    $num = $matches[1];
    $suffix = strtolower($matches[2]);
    switch ($suffix) {
        case "tb": // TeraByte
            return $num * 1099511627776;
        case "gb": // GigaByte
            return $num * 1073741824;
        case "mb": // MegaByte
            return $num * 1048576;
        case "kb": // KiloByte
            return $num * 1024;
        case "b": // Byte
        default:
            return $num;
    }
}

// Setup Forum Header
function forumheader($location = '')
{
    echo "<div class='rounded p-2'>
    <i class='fa fa-search' title='Search'></i>&nbsp;<a href='" . URLROOT . "/forum/search'>" . LANG["SEARCH"] . "</a>&nbsp; &nbsp;
     <b>" . LANG["FORUM_CONTROL"] . "</b>
    &middot; <a href='" . URLROOT . "/forum/viewunread'>" . LANG["FORUM_NEW_POSTS"] . "</a>
    &middot; <a href='" . URLROOT . "/forum/viewunread/true'>" . LANG["FORUM_MARK_READ"] . "</a>
    </div><br />";
    print("<div class='rounded p-1 g-4'>" . LANG["YOU_ARE_IN"] . ": &nbsp;<a href='" . URLROOT . "/forum'>" . LANG["FORUM"] . "</a> <b style='vertical-align:middle'>/ $location</b></div><br>");
}

// Mark all forums as read
function catch_up()
{
    if (!$_SESSION['id']) {
        return;
    }
    $userid = $_SESSION['id'];
    $topics_query = "SELECT id, lastpost FROM `forum_topics`";
    $res = DB::run($topics_query);
    foreach ($res as $arr) {
        $topicid = $arr["id"];
        $postid = $arr["lastpost"];
        $readposts_query = "SELECT id,lastpostread FROM `forum_readposts` WHERE userid=? AND topicid=?";
        $r = DB::run($readposts_query, [$userid,$topicid])->fetch(PDO::FETCH_ASSOC);

        if ($r) {
            $readposts_query = "INSERT INTO forum_readposts (userid, topicid, lastpostread) VALUES (?,?,?)";
            DB::run($readposts_query, [$userid, $topicid, $postid]);
        } else {
            if ($r["lastpostread"] < $postid) {
                $readposts_query = "UPDATE forum_readposts SET lastpostread=? WHERE id=?";
                DB::run($readposts_query, [$postid, $r["id"]]);
            }
        }
    }
}

// Returns The ID Of A Last Post In A Forum Or Otherwise 0
function get_forum_last_post($forumid)
{
    $topics_query = "SELECT lastpost FROM `forum_topics` WHERE forumid = ? ORDER BY lastpost DESC LIMIT 1";
    $res = DB::run($topics_query, [$forumid])->fetchColumn();
    if ($res) {
        return $res;
    } else {
        return 0;
    }
}

// LASTEST FORUM POSTS
function latestforumposts($sub_id = 0)
{
    ?>
    <div class="table-responsive tt_block">
        <table class="table table-striped ">
            <thead>
                <tr>
                    <th class="col-6">Latest Topic Title</th>
                    <th class="col-1">Replies</th>
                    <th class="col-1">Views</th>
                    <th class="col-1">Author</th>
                    <th class="col-5">Last Post</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // HERE GOES THE QUERY TO RETRIEVE DATA FROM THE DATABASE AND WE START LOOPING ///
                $topics_query= "SELECT * FROM `forum_topics` ORDER BY lastpost DESC LIMIT 8";
                $for = DB::run($topics_query);
                if ($for->rowCount() == 0) {
                    print("<b>No Latest Topics</b>");
                }
                foreach ($for as $topicarr) {
                    // Set minclass
                    $forums_query = "SELECT name,minclassread,guest_read FROM `forum_forums` WHERE id=? ";
                    $forum = DB::run($forums_query, [$topicarr['forumid']])->fetch(PDO::FETCH_ASSOC);
                    if ($forum && $_SESSION['class'] >= $forum["minclassread"] || $forum["guest_read"] == "yes") {
                        $posts_query = "SELECT count(id) FROM `forum_posts` WHERE topicid = ?";
                        $posts = DB::run($posts_query, [$topicarr["id"]])->fetchColumn();
                        $replies = max(0, $posts - 1);
                        $posts_query = "SELECT added,userid FROM `forum_posts` WHERE topicid=? ORDER BY id DESC LIMIT 1";
                        $arr = DB::run($posts_query, [$topicarr["id"]])->fetch(PDO::FETCH_ASSOC);
                        ?>
                        
                        <tr>
                            <td>
                                <b><a href='<?= URLROOT ?>/post/<?= $topicarr["id"] ?>/last/#last'><?= stripslashes(encodehtml($topicarr["subject"])) ?></a></b>
                            </td>
                            <td>
                                <?= $replies ?>
                            </td>
                            <td>
                                <?= $topicarr["views"] ?>
                            </td>
                            <td>
                                <b>
                                    <center><?= coloredname($topicarr["userid"]) ?></center>
                                </b>
                            </td>
                            <td>
                                <small><b><a href='<?= URLROOT ?>/post/<?= $topicarr["id"] ?>/last/#last'><?= stripslashes(encodehtml($topicarr["subject"])) ?></a></b>&nbsp;
                                    by&nbsp;<b><?= coloredname($arr["userid"]) ?></b></small><br><small style='white-space: nowrap'><b><?= user_timezone_date($arr["added"]) ?></b></small>
                            </td>
                        <tr>
                    <?php
                    }
                } ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Get last post info in a array return img & lastpost
function lastpostdetails($lastpostid)
{
    $posts_query = "SELECT added,topicid,userid FROM `forum_posts` WHERE id=?";
    $post_res = DB::run($posts_query, [$lastpostid])->fetch(PDO::FETCH_ASSOC);
    if ($post_res) {
        $post_arr = $post_res or showMessage('forum', "Bad forum last_post");
        $lastposterid = $post_arr["userid"];
        $lastpostdate = user_timezone_date($post_arr["added"]);
        $lasttopicid = $post_arr["topicid"];
        $lastposter = coloredname($lastposterid);
        $topics_query = "SELECT subject FROM `forum_topics` WHERE id=?";
        $topic_arr = DB::run($topics_query, [$lasttopicid])->fetch(PDO::FETCH_ASSOC);
        $lasttopic = stripslashes(htmlspecialchars($topic_arr['subject']));
        //cut last topic
        $latestleng = 10;
        $lastpost = "<small><a href='" . URLROOT . "/post/$lasttopicid/last/#last'><b>" . CutName($lasttopic, $latestleng) . "</b></a> by <a href='" . URLROOT . "/profile/$lastposterid'><b>$lastposter</b></a><br />$lastpostdate</small>";
        if ($_SESSION['id']) {
            $readposts_query = "SELECT count(id) FROM `forum_readposts` WHERE userid = ? AND topicid=?";
            $a = DB::run($readposts_query, [$_SESSION['id'],$lasttopicid])->fetchColumn();
        }
        //define the images for new posts or not on index
        if ($a && $a == $lastpostid) {
            $img = "<i class='fa fa-file-text' title='Read'></i>";
        } else {
            $img = "<i class='fa fa-file-text tticon-red' title='UnRead'></i>";
        }
    } else {
        $lastpost = "<span class='small'>No Posts</span>";
        $img = "folder";
    }

    $detail = [
        'img' => $img,
        'lastpost' => $lastpost,
    ];
    return $detail;
}

// Display Mod Options
function modoptions($topicid, $subject, $forumid, $locked, $sticky)
{   
    ?>
    <div class='tt_blockcenter rounded p-2'> 
        <p class='text-center'><?= LANG["FORUMS_MOD_OPTIONS"] ?></p>
        <form method='post' action='<?= URLROOT ?>/topic/rename/<?= $topicid ?>'>
           <p class='text-center'><?= LANG["RENAME"] ?></p>
           <input class='form-control fullwidth' type='text' name='subject' value='<?= stripslashes(htmlspecialchars($subject)) ?>' />
           <p class='text-center'><input type='submit'class='btn btn-sm ttbtn' value='<?= LANG["SUBMIT"] ?>' /></p>
        </form><br>
        <form method='post' action='<?= URLROOT ?>/topic/move/<?= $topicid ?>'>
           <p class='text-center'><?= LANG["MOVE"] ?></p>
            <select name='forumid' class='form-select'><?php

$forums_query = "SELECT id,name,minclasswrite FROM `forum_forums` ORDER BY name";
$res = DB::run($forums_query);

                foreach ($res as $arr) { ?>
                    <?php if ($arr["id"] != $forumid && $_SESSION['class'] >= $arr["minclasswrite"]) : ?>
                       <option value='<?= $arr["id"] ?>'><?= $arr["name"] ?></option>
                    <?php endif; ?>
               <?php } ?>
            </select>
            <p class='text-center'><input type='submit'class='btn btn-sm ttbtn' value='<?= LANG["SUBMIT"] ?>' /></p>
        </form>

    <?php
    print("<p class='text-center'>\n");
    if ($locked) {
        print(LANG["LOCKED"] . ": <a href='" . URLROOT . "/topic/action/$forumid/$topicid/unlock' title='Unlock'><i class='fa fa-unlock tticon-red' title='UnLock Topic'></i></a>\n");
    } else {
        print(LANG["LOCKED"] . ": <a href='" . URLROOT . "/topic/action/$forumid/$topicid/lock' title='Lock'><i class='fa fa-lock tticon' title='Lock Topic'></i></a>\n");
    }
    print("Delete Entire Topic: <a href='" . URLROOT . "/topic/delete/$topicid' title='Delete'><i class='fa fa-trash tticon-red' title='Delete Topic'></i></a>\n");
    if ($sticky) {
        print(LANG["STICKY"] . ": <a href='" . URLROOT . "/topic/action/$forumid/$topicid/unsticky' title='UnStick'><i class='fa fa-exclamation tticon-red' title='Unstick Topic'></i></a>\n");
    } else {
        print(LANG["STICKY"] . ": <a href='" . URLROOT . "/topic/action/$forumid/$topicid/sticky' title='Stick'><i class='fa fa-exclamation tticon' title='stick Topic'></i></a>\n");
    }
    print("</p>");
    print("</div>");
}

// Get Poster Details
function posterdetails($userid)
{
    $posts_query = "SELECT count(id) FROM `forum_posts` WHERE userid = ?";
    $forumposts = DB::run($posts_query, [$userid])->fetchColumn();
    $users_query = "SELECT * FROM `users` WHERE id = ?";
    $arr2 = DB::run($users_query, [$userid])->fetch(PDO::FETCH_ASSOC);
    $postername = coloredname($userid);
    $quotename = $arr2["username"];
    if ($postername == "") {
        $by = "Deluser";
        $title = "Deleted Account";
        $privacylevel = "strong";
        $usersignature = "";
        $userdownloaded = "0";
        $useruploaded = "0";
        $avatar = "";
    } else {
        $avatar = htmlspecialchars($arr2["avatar"]);
        $userdownloaded = mksize($arr2["downloaded"]);
        $useruploaded = mksize($arr2["uploaded"]);
        $privacylevel = $arr2["privacy"];
        $usersignature = stripslashes(format_comment($arr2["signature"]));
        $userratio = $arr2["downloaded"] > 0 ? number_format($arr2["uploaded"] / $arr2["downloaded"], 1) : "---";
        if (!$arr2["country"]) {
            $usercountry = "unknown";
        } else {
            $countries_query = "SELECT * FROM `countries` WHERE id=? LIMIT 1";
            $usercountry =DB::run($countries_query, [$arr2['country']])->fetch(PDO::FETCH_ASSOC);
        }
        $title = format_comment($arr2["title"]);
        $donated = $arr2['donated'];
        $by = "<a href='" . URLROOT . "/profile/$userid'>$postername</a>" . ($donated > 0 ? "<i class='fa fa-star' aria-hidden='true' style='color:orange' title='Donated'></i>" : "") . "";
        //hide stats, but not from staff
        if ($privacylevel == "strong" && $_SESSION['control_panel'] != "yes") {
            $useruploaded = "---";
            $userdownloaded = "---";
            $userratio = "---";
        }
    }
    if (!$avatar) {
        $avatar = URLROOT . "/assets/images/misc/default_avatar.webp";
    }

    $details = [
        'by' => $by,
        'title' => $title,
        'privacylevel' => $privacylevel,
        'usersignature' => $usersignature,
        'userdownloaded' => $userdownloaded,
        'useruploaded' => $useruploaded,
        'avatar' => $avatar,
        'forumposts' => $forumposts,
        'userratio' => $userratio,
        'usercountry' => $usercountry,
        'quotename' => $quotename
    ];

    return $details;
}

// Get Group Name
function get_user_class_name($i)
{
    $groups_query = "SELECT level FROM `groups` WHERE group_id=?";
    $row = DB::run($groups_query, [$i])->fetch(PDO::FETCH_ASSOC);
    if ($i == $_SESSION['class']) {
        return  $row['level'];
    }
    return $row['level'];
}

// Active Link
function activelink($page, $message = '')
{
    if ($message == $page && isset($_GET['type']) == $page) {
        $active = 'ttbtnactive';
        return $active;
    }
    $active = $_GET['url'] == $page ? 'ttbtnactive' : 'btn btn-sm ttbtn'; // active todo
    return $active;
}

// Profile Navbar
function usermenu($id, $page = false)
{
    $messageactive = $page == 'messages' ? 'ttbtnactive' : 'btn btn-sm ttbtn';
    ?>
    <p>
        <a href='<?php echo URLROOT; ?>/profile/<?= $id ?>'><button type="button" class="<?= activelink('profile') ?>"><?= LANG["PROFILE"] ?></button></a>
        <?php if ($_SESSION['id'] == $id) : ?>
            <a href='<?= URLROOT ?>/message/mailbox'><button type="button" class="<?= $messageactive ?>"><?= LANG['MESSAGES'] ?></button></a>
        <?php else : ?>
            <a href="<?= Url("message/create/$id") ?>"><button type="button" class="<?= activelink("message/create/$id") ?>"><?= LANG["PM"] ?></button></a>
        <?php endif; ?>

        <?php if ($_SESSION['view_torrents'] != "yes" && CONFIG['MEMBERSONLY']) { ?>
            <a href='<?= URLROOT ?>/peer/seeding/<?= $id ?>'><button type="button" class="<?= activelink("peer/seeding/$id") ?>"><?= LANG["SEEDING"] ?></button></a>
            <a href='<?= URLROOT ?>/peer/leeching/<?= $id ?>'><button type="button" class="<?= activelink("peer/leeching/$id") ?>"><?= LANG["DOWNLOADING"] ?></button></a>
        <?php } ?>
        <span style="float:right;">
        <?php $profile = $_GET['url']; ?>
        <?php if ($profile == "profile/$id") { ?>
                <a href="#" data-bs-toggle="modal" data-bs-target="#reportModal"><button type="button" class="<?= activelink('report') ?>"><?= LANG['REPORT'] ?></button></a>
                <a href='<?= URLROOT ?>/warning/<?= $id ?>'><button type="button" class="<?= activelink('warning') ?>"><?= LANG['WARN'] ?></button></a>
        <?php } ?>
        </span>
    </p>
    <?php
}

// Torrent Navbar
function torrentmenu($id, $external = 'no')
{
     ?>
    <a href='<?= URLROOT ?>/torrent/<?= $id ?>'><button type="button" class="<?= activelink('torrent') ?>"><?= LANG["BACK"] ?></button></a>

    <?php if ($external != 'yes') { ?>
        <a href='<?= URLROOT ?>/peer/torrent/<?= $id ?>'><button type="button" class="<?= activelink("peer/torrent/$id") ?>"><?= LANG["PEERS"] ?></button></a>
    <?php }
    ?>
    <br><br>
    <?php
}

// Sort Message Array By Type
function msgdetails($type, $arr = [])
{
    // Whos Sender
    if ($arr["sender"] == $_SESSION['id']) {
        $sender = "Yourself";
    } elseif (intval($arr["sender"])) {
        $sender = "<a href=\"/profile/$arr[sender]\">" . ($arr["sender"] ? coloredname($arr["sender"]) : "[Deleted]") . "</a>";
    } else {
        $sender = LANG["SYSTEM"];
    }

    // Whos Reciever
    if ($arr["receiver"] == $_SESSION['id']) {
        $receiver = "Yourself";
    } elseif (intval($arr["receiver"])) {
        $receiver = "<a href=\"" . URLROOT . "/profile/$arr[receiver]\">" . ($arr["receiver"] ? coloredname($arr["receiver"]) : "[" . LANG['DEL'] . "]") . "</a>";
    } else {
        $receiver = LANG["SYSTEM"];
    }

    // Subject
    $subject = "<a href='" . URLROOT . "/message/$arr[id]/$type'><b>" . format_comment($arr["subject"]) . "</b></a>";
    $added = user_timezone_date($arr["added"]);

    // Unread
    if ($arr["unread"] == "yes") {
        $unread = "<i class='fa fa-file-text tticon-red' title='" . LANG['UNREAD'] . "'></i>";
    } else {
        $unread = "<i class='fa fa-file-text' title='" . LANG['READ'] . "'></i>";
    }

    // Return In Array
    $arr = [
        'sender' => $sender,
        'receiver' => $receiver,
        'subject' => $subject,
        'unread' => $unread,
        'added' => $added
    ];
    return $arr;
}

// Get Comment Details
function userpostdetails($row, $type = null)
{
    $postername = coloredname($row["id"]);
    if ($postername == "System") {
        $title = "";
        $avatar = "";
        $usersignature = "";
        $userdownloaded = "";
        $useruploaded = "";
        $privacylevel = 'strong';
    } else {
        $privacylevel = $row["privacy"];
        $avatar = htmlspecialchars($row["avatar"]);
        $title = format_comment($row["title"]);
        $usersignature = stripslashes(format_comment($row["signature"]));
        $userdownloaded = mksize($row["downloaded"]);
        $useruploaded = mksize($row["uploaded"]);
    }
    $userratio = $row["downloaded"] > 0 ? number_format($row["uploaded"] / $row["downloaded"], 1) : "---";

    $commenttext = $row["body"] != '' ? format_comment($row["body"]) : format_comment($row["text"]);

    if (!$avatar) {
        $avatar = URLROOT . "/assets/images/misc/default_avatar.webp";
    }

    $edit = null;
    if ($_SESSION['edit_torrents'] == 'yes' || $_SESSION['edit_news'] == 'yes' || $_SESSION['id'] == $row['user']) {
        $edit = '<a href="javascript:;" class="editAttr" data-bs-toggle="modal" data-bs-target="#editModal" data-body="' . $commenttext . '" data-postid="' . $row['id'] . '"><i class="fa fa-edit"></i></a>';
    }

    $delete = null;
    if ($_SESSION['delete_torrents'] == 'yes' || $_SESSION['delete_news'] == 'yes') {
        $delete = '<a href="' . URLROOT . '/post/delete/' . $row['id'] . '"><i class="fa fa-trash" title="' . LANG['DEL'] . '" aria-hidden="true"></i></a>&nbsp;';
    }

    $editcomment = null;
    if ($type == "torrent" && $_SESSION['edit_torrents'] == 'yes' || $type == "news"  || $type == "req" && $_SESSION['edit_news'] == 'yes' || $_SESSION['id'] == $row['user']) {
        $editcomment = '<a href="javascript:;" class="addAttr" data-bs-toggle="modal" data-bs-target="#addModal" 
        data-id=' . $row['id'] . '
        data-text=' . $row['text'] . ' >
        <i class="fa fa-edit"></i>
        </a>&nbsp;';
    }

    $deletecomment = null;
    if ($type == "torrent" && $_SESSION['delete_torrents'] == 'yes' || $type == "req" || $type == "news" && $_SESSION['delete_news'] == 'yes') {
        $deletecomment = '<a href="' . URLROOT . '/comment/delete/' . $row["id"] . '/' . $type . '"><i class="fa fa-trash" title="' . LANG['DEL'] . '" aria-hidden="true"></i></a>&nbsp;';
    }

    $data = [
        'postername' => $postername,
        'title' => $title,
        'avatar' => $avatar,
        'usersignature' => $usersignature,
        'userdownloaded' => $userdownloaded,
        'useruploaded' => $useruploaded,
        'privacylevel' => $privacylevel,
        'userratio' => $userratio,
        'commenttext' => $commenttext,
        'edit' => $edit,
        'delete' => $delete,
        'editcomment' => $editcomment,
        'deletecomment' => $deletecomment
    ];
    return $data;
}

// Display Username
function coloredname($id)
{
    $users_query =
        "SELECT u.id, u.username, u.donated, u.warned, u.enabled, 
        g.Color
        FROM `users` `u` 
        INNER JOIN `groups` `g` 
        ON g.group_id=u.class 
        WHERE id ='" . $id . "'"
    ;
    $classy = DB::run($users_query)->fetch();

    if ($classy) {
        $gcolor = $classy['Color'];
        if ($classy['donated'] > 0) {
            $star = "<i class='fa fa-star' aria-hidden='true' style='color:orange' title='" . LANG['DONATED'] . "'></i>";
        } else {
            $star = "";
        }
        if ($classy['warned'] == "yes") {
            $warn = "<img src='" . URLROOT . "/assets/images/misc/warn.webp' alt='" . LANG['WARN'] . "' border='0'>";
        } else {
            $warn = "";
        }
        if ($classy['enabled'] == "no") {
            $disabled = "<img src='" . URLROOT . "/assets/images/misc/disabled.webp' title='" . LANG['DISABLED'] . "' border='0'>";
        } else {
            $disabled = "";
        }

        $name = htmlspecialchars($classy["username"]);

        return stripslashes("<a href='" . URLROOT . "/profile/" . $classy['id'] . "'><font color='#" . $gcolor . "'>" . $name . "" . $star . "" . $warn . "" . $disabled . "</font></a>");
    } else {
        return LANG['SYSTEM'];
    }
}

function pagination($rowsperpage, $countresult, $url)
{
    // Total Page Number 
    $pagetotal = ceil($countresult / $rowsperpage);

    // Get Page
    $currentpage = (isset($_GET['page'])) ? trim(filter_input(INPUT_GET, 'page')) : 0;
    if ($currentpage == 'last') {
        $page =  "$pagetotal";
    } elseif ($currentpage > 0) {
        $page = $currentpage;
    } else {
        $page = 0;
    }

    // Limit 
    $offset = max(0, ($page * $rowsperpage) - $rowsperpage);
    $limit = $rowsperpage;

    // Here we generates the range of the page numbers which will display adjacents.
    $adjacents = 3;
    if ($pagetotal <= (1 + ($adjacents * 2))) {
        $start = 1;
        $end   = $pagetotal;
    } else {
        if (($page - $adjacents) > 1) {
            if (($page + $adjacents) < $pagetotal) {
                $start = ($page - $adjacents);
                $end   = ($page + $adjacents);
            } else {
                $start = ($pagetotal - (1 + ($adjacents * 2)));
                $end   = $pagetotal;
            }
        } else {
            $start = 1;
            $end   = (1 + ($adjacents * 2));
        }
    }

    // Display Buttons
    $link = '';
    if ($pagetotal > 1) {
        $link .= '<ul class="pagination pagination-sm justify-content-left">';
        // Link of the first page
        $link .= "<li class='page-item " . ($page <= 1 ? 'tt_disabled' : '') . "'>";
        $link .= "<a class='page-link' href='" . $url . "page=1'><<</a>";
        $link .= '</li>';
        // Link of the previous page
        $link .= "<li class='page-item " . ($page <= 1 ? 'tt_disabled' : '') . "'>";
        $link .= "<a class='page-link' href='" . $url . "page=" . ($page > 1 ? ($page - 1) : 1) . "'><</a>";
        $link .= "</li>";
        // Links of the pages with page number
        for ($i = $start; $i <= $end; $i++) {
            $link .= "<li class='page-item " . ($i == $page ? 'active' : '') . "'>";
            $link .= "<a class='page-link' href='" . $url . "page=$i'>$i</a>";
            $link .= "</li>";
        }
        // Link of the next page
        $link .= "<li class='page-item " . ($page >= $pagetotal ? 'tt_disabled' : '') . "'>";
        $link .= "<a class='page-link' href='" . $url . "page=" . ($page < $pagetotal ? ($page + 1) : $pagetotal) . "'>></a>";
        $link .= "</li>";
        // Link of the last page
        $link .= "<li class='page-item " . ($page >= $pagetotal ? 'tt_disabled' : '') . "'>";
        $link .= "<a class='page-link' href='" . $url . "page=$pagetotal'>>>  ";
        $link .= "</a>";
        $link .= "</li>";
        $link .= '</ul>';
    }

    // Return Array
    return array($link, "LIMIT $offset, $limit");
}

// Display Captcha
function captcha_html()
{
    if (CONFIG['CAPTCHA_ON']) {
        $captcha_form = "<center><div class='g-recaptcha' data-theme='light' data-sitekey='" . CONFIG['CAPTCHA_KEY'] . "'></div><center>";
        return $captcha_form;
    }
}


// Function That Returns The Health Level Of A Torrent
function health($leechers, $seeders)
{
    if (($leechers == 0 && $seeders == 0) || ($leechers > 0 && $seeders == 0)) {
        return 0;
    } elseif ($seeders > $leechers) {
        return 10;
    }
    $ratio = $seeders / $leechers * 100;
    if ($ratio > 0 && $ratio < 15) {
        return 1;
    } elseif ($ratio >= 15 && $ratio < 25) {
        return 2;
    } elseif ($ratio >= 25 && $ratio < 35) {
        return 3;
    } elseif ($ratio >= 35 && $ratio < 45) {
        return 4;
    } elseif ($ratio >= 45 && $ratio < 55) {
        return 5;
    } elseif ($ratio >= 55 && $ratio < 65) {
        return 6;
    } elseif ($ratio >= 65 && $ratio < 75) {
        return 7;
    } elseif ($ratio >= 75 && $ratio < 85) {
        return 8;
    } elseif ($ratio >= 85 && $ratio < 95) {
        return 9;
    } else {
        return 10;
    }
}

// Return Tags
function get_tags($id) 
{
    $tagtorrent_query = "SELECT type1, type2, type3 FROM `tagtorrent` WHERE torrentid=?";
    $tag =  DB::run($tagtorrent_query, [$id])->fetch(PDO::FETCH_ASSOC);
    $tags = '';
    if (!empty($tag['type1'])) {
        $tags .= "&nbsp;<a href=".URLROOT."/tag/$tag[type1]><span class='badge border ttbtn'>$tag[type1]</span></a>&nbsp;";
    }
    if (!empty($tag['type2'])) {
        $tags .= "<a href=".URLROOT."/tag/$tag[type2]><span class='badge border ttbtn'>$tag[type2]</span></a>&nbsp;";
    }
    if (!empty($tag['type3'])) {
        $tags .= "<a href=".URLROOT."/tag/$tag[type3]><span class='badge border ttbtn'>$tag[type3]</span></a>&nbsp;";
    }
    
    return $tags;
}

// Function that assigns a color based on the value of the ratio
function get_ratio_color($ratio)
{
   if ($ratio == '---') {
       return "<font color='FFFFFF'>$ratio</font>";
   }
   if ($ratio < 0.5) {
       return "<font color='#fc0313'>$ratio</font>";
   }
   if ($ratio < 1) {
       return "<font color='#fc6203'>$ratio</font>";
   }
   if ($ratio < 1.5) {
       return "<font color='#ebc334'>$ratio</font>";
   }
   if ($ratio < 2) {
       return "<font color='#1a7d09'>$ratio</font>";
   }
   return "<font color='#0d6efd'>$ratio</font>";
}

// VIEW ANON
function anon($data)
{
    if ($data["anon"] == "yes" && !$data['owned']) {
        echo LANG["ANON"];
    } elseif ($data["username"]) { ?>
        <a href='profile/<?php echo $data["owner"]; ?>'><?= coloredname($data["owner"]) ?></a> <?php
    } else { ?>
        <?= LANG["SYSTEM"] ?> <?php
    }
}

// VIEW MAGNET LINK
function magnetlink($data)
{
    if (!CONFIG['FORCETHANKS'] && $data["external"] == 'yes' || $data["external"] == 'yes' && $data["vip"] == 'yes' && $_SESSION['class'] >= CONFIG['_VIP']) {
        print("&nbsp;<a href=\"magnet:?xt=urn:btih:" . $data["info_hash"] . "&dn=" . $data["filename"] . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-1x' style='color:green' title='" . LANG["DOWNLOADED"] . "'></i></a>&nbsp;");
    } else {
        $likes_query = "SELECT liked, type, user FROM `likes` WHERE topicid=? ORDER BY id DESC LIMIT 1";
        $like = DB::run($likes_query, [$data['id'], 'torrent', $_SESSION['id']])->fetch(PDO::FETCH_ASSOC);
        if ($like) {
            if ($data["external"] == 'yes') {
                if ($_SESSION['can_download'] == 'yes') {
                    print("&nbsp;<a href=\"magnet:?xt=urn:btih:" . $data["info_hash"] . "&dn=" . $data["filename"] . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-1x' style='color:green' title='" . LANG["DOWNLOADED"] . "'></i></a>&nbsp;");
                }
            }
        } else {
            if ($_SESSION['id'] != $data["owner"]) {
                print("<a href='" . URLROOT . "/like/$data[stmt][id]/torrent'><button  class='btn btn-sm ttbtn'>" . LANG["THANKS"] . "</button></a><br>");
            } elseif ($data["vip"] == 'yes' && $_SESSION['class'] <= CONFIG['_VIP']) {

            } elseif ($data["external"] == 'yes') {
                print("&nbsp;<a href=\"magnet:?xt=urn:btih:" . $data["info_hash"] . "&dn=" . $data["filename"] . "&tr=udp://tracker.openbittorrent.com&tr=udp://tracker.publicbt.com\"><i class='fa fa-magnet fa-1x' style='color:green' title='" . LANG["DOWNLOADED"] . "'></i></a>&nbsp;");
            }
        }
    }
}

// VIEW NFO BY ID
function displaynfo($id)
{
    $nfofilelocation = NFOS_PATH . "/$id.nfo";
    $filegetcontents = file_get_contents($nfofilelocation);
    $nfo = $filegetcontents;
    if ($nfo) {
        $trans = array(
            "\x80" => "&#199;", "\x81" => "&#252;", "\x82" => "&#233;", "\x83" => "&#226;", "\x84" => "&#228;", "\x85" => "&#224;", "\x86" => "&#229;", "\x87" => "&#231;", "\x88" => "&#234;", "\x89" => "&#235;", "\x8a" => "&#232;", "\x8b" => "&#239;", "\x8c" => "&#238;", "\x8d" => "&#236;", "\x8e" => "&#196;", "\x8f" => "&#197;", "\x90" => "&#201;",
            "\x91" => "&#230;", "\x92" => "&#198;", "\x93" => "&#244;", "\x94" => "&#246;", "\x95" => "&#242;", "\x96" => "&#251;", "\x97" => "&#249;", "\x98" => "&#255;", "\x99" => "&#214;", "\x9a" => "&#220;", "\x9b" => "&#162;", "\x9c" => "&#163;", "\x9d" => "&#165;", "\x9e" => "&#8359;", "\x9f" => "&#402;", "\xa0" => "&#225;", "\xa1" => "&#237;",
            "\xa2" => "&#243;", "\xa3" => "&#250;", "\xa4" => "&#241;", "\xa5" => "&#209;", "\xa6" => "&#170;", "\xa7" => "&#186;", "\xa8" => "&#191;", "\xa9" => "&#8976;", "\xaa" => "&#172;", "\xab" => "&#189;", "\xac" => "&#188;", "\xad" => "&#161;", "\xae" => "&#171;", "\xaf" => "&#187;", "\xb0" => "&#9617;", "\xb1" => "&#9618;", "\xb2" => "&#9619;",
            "\xb3" => "&#9474;", "\xb4" => "&#9508;", "\xb5" => "&#9569;", "\xb6" => "&#9570;", "\xb7" => "&#9558;", "\xb8" => "&#9557;", "\xb9" => "&#9571;", "\xba" => "&#9553;", "\xbb" => "&#9559;", "\xbc" => "&#9565;", "\xbd" => "&#9564;", "\xbe" => "&#9563;", "\xbf" => "&#9488;", "\xc0" => "&#9492;", "\xc1" => "&#9524;", "\xc2" => "&#9516;", "\xc3" => "&#9500;",
            "\xc4" => "&#9472;", "\xc5" => "&#9532;", "\xc6" => "&#9566;", "\xc7" => "&#9567;", "\xc8" => "&#9562;", "\xc9" => "&#9556;", "\xca" => "&#9577;", "\xcb" => "&#9574;", "\xcc" => "&#9568;", "\xcd" => "&#9552;", "\xce" => "&#9580;", "\xcf" => "&#9575;", "\xd0" => "&#9576;", "\xd1" => "&#9572;", "\xd2" => "&#9573;", "\xd3" => "&#9561;", "\xd4" => "&#9560;",
            "\xd5" => "&#9554;", "\xd6" => "&#9555;", "\xd7" => "&#9579;", "\xd8" => "&#9578;", "\xd9" => "&#9496;", "\xda" => "&#9484;", "\xdb" => "&#9608;", "\xdc" => "&#9604;", "\xdd" => "&#9612;", "\xde" => "&#9616;", "\xdf" => "&#9600;", "\xe0" => "&#945;", "\xe1" => "&#223;", "\xe2" => "&#915;", "\xe3" => "&#960;", "\xe4" => "&#931;", "\xe5" => "&#963;",
            "\xe6" => "&#181;", "\xe7" => "&#964;", "\xe8" => "&#934;", "\xe9" => "&#920;", "\xea" => "&#937;", "\xeb" => "&#948;", "\xec" => "&#8734;", "\xed" => "&#966;", "\xee" => "&#949;", "\xef" => "&#8745;", "\xf0" => "&#8801;", "\xf1" => "&#177;", "\xf2" => "&#8805;", "\xf3" => "&#8804;", "\xf4" => "&#8992;", "\xf5" => "&#8993;", "\xf6" => "&#247;",
            "\xf7" => "&#8776;", "\xf8" => "&#176;", "\xf9" => "&#8729;", "\xfa" => "&#183;", "\xfb" => "&#8730;", "\xfc" => "&#8319;", "\xfd" => "&#178;", "\xfe" => "&#9632;", "\xff" => "&#160;",
        );
        $trans2 = array("\xe4" => "&auml;", "\xF6" => "&ouml;", "\xFC" => "&uuml;", "\xC4" => "&Auml;", "\xD6" => "&Ouml;", "\xDC" => "&Uuml;", "\xDF" => "&szlig;");
        $all_chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $last_was_ascii = false;
        $tmp = "";
        $nfo = $nfo . "\00";
        for ($i = 0; $i < (strlen($nfo) - 1); $i++) {
            $char = $nfo[$i];
            if (isset($trans2[$char]) and ($last_was_ascii or strpos($all_chars, ($nfo[$i + 1])))) {
                $tmp = $tmp . $trans2[$char];
                $last_was_ascii = true;
            } else {
                if (isset($trans[$char])) {
                    $tmp = $tmp . $trans[$char];
                } else {
                    $tmp = $tmp . $char;
                }
                $last_was_ascii = strpos($all_chars, $char);
            }
        }
        echo "<p class='text-center'><b>NFO:</b></p>";
        print("<textarea class='form-control' style='min-width:700px;height:700px;' readonly='readonly'>" . stripslashes($tmp) . "</textarea>");
    } else {
        print(LANG["NO_NFO_ERROR"] );
    }
}

function multiIds($value)
{
    $ids = $_POST[$value];
    if (!$ids) {
        showMessage(URLROOT, LANG["MISSING_FORM_DATA"]);
    }
    $int_array = array_map('intval', $ids);
    $string_of_ids = implode(', ', $int_array);

    return $string_of_ids;
}

// Check $_POST Input
function Input1($value, $type = null)
    {
        if ($type == 'email') {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                showMessage(URLROOT, LANG["INVALID_EMAIL_ADDRESS"]);
            }
            return $_POST[$value];
        }
        if ($type == 'int') {
            if (filter_var($_POST[$value], FILTER_VALIDATE_INT)) {
                return $_POST[$value];
            } else {
                return 0;
            }
        }
        if (isset($_POST[$value])) {
            return trim(strip_tags(filter_input(INPUT_POST, $value)));
        }
        return false;
}

// Goggle v2 Captcha
function captcha_response($captcha)
{
    if (CONFIG['CAPTCHA_ON']) {
        if (!$captcha) {
            showMessage('login', LANG['CAPTCHA']);
        }
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . CONFIG['CAPTCHA_SECRET'] . '&response=' . $captcha);
        $responseData = json_decode($verifyResponse);
        if ($responseData->success) {
            // its successfull so let it go
        }
    }
}