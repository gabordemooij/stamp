

STAMP
=====

[![Build Status](https://secure.travis-ci.org/gabordemooij/stamp.png)](http://travis-ci.org/gabordemooij/stamp)

Stamp is micro template library orignally written by Gabor de Mooij.

Stamp t.e. is a new kind of Template Engine for PHP. 
You don't need to learn a new 
template language and you get 100% separation 
between presentation logic and your HTML templates.

How it Works
------------

Stamp t.e. is a string 
manipulation based template engine. This is a different 
approach from most template engines 
which use inline templating. In Stamp t.e. 
you set markers in your template (HTML comments), 
these are then used to manipulate the template from the outside. 

What does it look like
----------------------


A cut point maker marks a region in the 
template that will be cut out from the template 
and stored under the specified ID. 

    <div>
    <!-- cut:diamond -->
    <img src="diamond.gif" />
    <!-- /cut:diamond -->
    </div>


Now pass the template to StampTE:

    $se = new StampTE($templateHTML);

To obtain the diamond image:

    echo $se->get('diamond');

Result:


    <img src="diamond.gif" />



More info: http://www.stampte.com



Advantages
----------

* Clean, code-free HTML templates, No PHP in your HTML
* Compact presentation logic free of any HTML
* No new syntax to learn, uses basic HTML markers already in use by many frontend developers to clarify document structure
* Templates do not have to be converted to be used with PHP logic (toll free template upgrades)
* Templates are presentable before integration because they may contain dummy data which is removed by StampTE
* Easy to exchange templates, templates are ready to use
* Very suitable for advanced UI development and complex templates for games
* Templates become self-documenting, PHP code becomes more readable (less bugs)
* Automatically strips HTML comments
* Integrated caching system
* Automatically escapes strings for Unicode (X)HTML documents
* Just ONE little file
* Unit tested, high quality code
* Open Source, BSD license



