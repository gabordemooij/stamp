<?php

require "../Stamp.php";

$current = "news";
$tabs = array("home.html"=>"homepage","news.html"=>"news","about.html"=>"about");


$template = '

    <ul class="tabs">
        <!-- tab -->
            <li>
                <a class="#active#" href="#href#">#tab#</a>
            </li>
        <!-- /tab -->
    </ul>


';

$tabs = array("home.html"=>"homepage","news.html"=>"news","about.html"=>"about");
$s = new Stamp($template);
$current = "news";
$menu = "";
foreach($tabs as $lnk=>$t)
    $menu .= $s->copy("tab")->put("href",$lnk)->put("tab",$t)->put("active",($current==$t)?"active":"inactive");

echo $s->replace("tab",$menu);