<?php

/** EXAMPLES **/
require "stamps.php";

function demo1() {
    $current = "news";
    $tabs = array("home.html"=>"homepage","news.html"=>"news","about.html"=>"about");
    ?>
      <ul class="tabs">
        <?php foreach($tabs as $lnk=>$t): ?>
            <li>
                <a class="
                    <?php if ($current==$t): ?>
                        active
                    <?php else: ?>
                        inactive
                    <?php endif; ?>
                " href="<?php echo $lnk; ?>">
                    <?php echo $t; ?>
                </a>
            </li>
        <?php endforeach; ?>
     </ul>
    <?php

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

    foreach($tabs as $lnk=>$t)
        $menu .= $s->copy("tab")->put("href",$lnk)->put("tab",$t)->put("active",($current==$t)?"active":"inactive");

    echo $s->replace("tab",$menu);
    /**/
};

function demo2() {
    $base = '
    <head>
        <title>
            Here is the title:
            <!-- title -->
                Default title [will be replaced with $content]
            <!-- /title -->
        </title>
    </head>
    <body>
        <div id=content>
            <!-- content -->
                Default content [will be replaced with $content]
            <!-- /content -->
        </div>
        <div class=sidebar>
            <!-- sidebar -->
                Sidebar has no content [will be replaced with $sidebar]
            <!-- /sidebar -->
        </div>
        <!-- footer -->
            Should remain untouched...
        <!-- /footer -->
    </body>
    ';

    $content = '
    <!-- title -->
        My title
    <!-- /title -->
    <!-- content -->
        My body
    <!-- /content -->
    ';

    $sidebar = '
    <!-- sidebar -->
        Sidebar contents
    <!-- /sidebar -->
    ';

    $tpl = new Stamp($base);
    $tpl->extendWith($content)->extendWith($sidebar);
    print "<hr><pre>".htmlspecialchars($tpl)."</pre>";
};

function test1() {
    $content = '
    <!-- title -->
        My title
    <!-- /title -->
    <!-- title -->
        My second title
    <!-- /title -->
    ';

    $tpl = new Stamp($content);
    $tpl->replace('title', 'my two titles');
    return $tpl;

}

function run_tests() {
    assert(trim(test1()) == "my two titles\n    my two titles");
}

if($_GET['run_tests']) {
    run_tests();
    print "<br>Tests: DONE";
} else {
    demo1();
    demo2();
}
