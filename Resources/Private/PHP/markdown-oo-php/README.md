What is Markdown?
=================

Markdown is a text-to-HTML conversion tool for web writers.
It is intended to be as easy-to-read and easy-to-write as is feasible.

Readability, however, is emphasized above all else.
A Markdown-formatted document should be publishable as-is, as plain text,
without looking like it’s been marked up with tags or formatting instructions.

See [official website](http://daringfireball.net/projects/markdown/syntax) for syntax.


What is markdown-oo-php?
========================

It's an object-oriented PHP library capable of converting markdown text to XHTML.


Quick start
=========

Library has two entities: _Text_ and _Filter_
_Text_ represents a piece of text which can be both markdown and html.
_Filter_ is responsible for actual transformation.

In most cases, _Text_ is enough for simple usage.

    require_once 'Markdown/Text.php';

    // create an instance
    $text = new Markdown_Text();

    // set plaintext
    $text->setMarkdown($markdown);

    // or just
    $text = new Markdown_Text($markdown);

    // now you can output html
    echo $text->getHtml();

    // or just
    echo $text;


Advanced usage
==============

Internally, _Filter_ uses a set of filters which extends Markdown_Filter.
A filter is an object which can accept markdown text and return html.
You can write your own filters and use like this:

    $filters = array(
        'Linebreak',            // a built-in filter
        new MyCustomFilter(),   // child of Markdown_Filter
    );
    Markdown_Filter::setDefaultFilters($filters);

    // all transformations now use the custom filter
    echo new Markdown_Text('**Markdown is great!**');

    // you can get current filters set
    Markdown_Filter::getDefaultFilters();


Graceful fallback
=============

There is a special filter which is automatically executed after others.
IIt's called Fallback. Its job is to launch original Markdown.pl script and process given text using it.
This way the library will always stay usable during development process.

You can easily disable this behavior:

    Markdown_Filter::useFallbackFilter(false);

    // or just use your own filters (Fallback filter will not be appended)
    Markdown_Filter::setDefaultFilters($filters);


Requirements
===========

  *  PHP  >= 5.3
  *  Perl >= 5.6, if you gonna use Fallback filter.


Contribution
==========

  1.  [Fork me](https://github.com/garygolden/markdown-oo-php)
  2.  [Mail me](mailto:max@garygolden.me)

http://www.garygolden.me
