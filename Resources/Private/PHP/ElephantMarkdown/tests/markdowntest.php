<?php

require_once '../markdown.php';

class ElephantMarkdownTest extends PHPUnit_Framework_TestCase
{

    public function testMixedHTML()
    {
        $this->assertEquals(<<<HTML
<p>This is a regular paragraph.</p>

<table>
    <tr>
        <td>Foo</td>
    </tr>
</table>

<p>This is another regular paragraph.</p>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
This is a regular paragraph.

<table>
    <tr>
        <td>Foo</td>
    </tr>
</table>

This is another regular paragraph.
MD
        ));
    }

    public function testEscaping()
    {
        $this->assertEquals("<p>http://images.google.com/images?num=30&amp;q=larry+bird</p>\n", ElephantMarkdown::parse("http://images.google.com/images?num=30&q=larry+bird"));
        $this->assertEquals("<p>&copy;</p>\n", ElephantMarkdown::parse("&copy;"));
        $this->assertEquals("<p>AT&amp;T</p>\n", ElephantMarkdown::parse("AT&T"));
        $this->assertEquals("<p>4 &lt; 5</p>\n", ElephantMarkdown::parse("4 < 5"));
    }

    public function testParagraphs()
    {
        $this->assertEquals(<<<HTML
<p>This is a paragraph</p>

<p>This is another paragraph</p>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
This is a paragraph

This is another paragraph
MD
        ));
    }

    public function testLineBreaks()
    {
        $this->assertEquals(<<<HTML
<p>This is a paragraph<br />
with a line break</p>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
This is a paragraph  
with a line break
MD
        ));
    }

    public function testH1Setext()
    {
        $this->assertEquals("<h1>This is an H1</h1>\n",
            ElephantMarkdown::parse(
                <<<MD
This is an H1
=============
MD
        ));
    }

    public function testH2Setext()
    {
        $this->assertEquals("<h2>This is an H2</h2>\n",
            ElephantMarkdown::parse(
                <<<MD
This is an H2
-------------
MD
        ));
    }

    public function testH1Atx()
    {
        $this->assertEquals(
            "<h1>This is an H1</h1>\n", ElephantMarkdown::parse("# This is an H1")
        );
        $this->assertEquals(
            "<h1>This is an H1</h1>\n", ElephantMarkdown::parse("# This is an H1#")
        );
    }

    public function testH2Atx()
    {
        $this->assertEquals(
            "<h2>This is an H2</h2>\n", ElephantMarkdown::parse("## This is an H2")
        );
        $this->assertEquals(
            "<h2>This is an H2</h2>\n", ElephantMarkdown::parse("## This is an H2#")
        );
    }

    public function testH3Atx()
    {
        $this->assertEquals(
            "<h3>This is an H3</h3>\n", ElephantMarkdown::parse("### This is an H3")
        );
        $this->assertEquals(
            "<h3>This is an H3</h3>\n", ElephantMarkdown::parse("### This is an H3#")
        );
    }

    public function testH4Atx()
    {
        $this->assertEquals(
            "<h4>This is an H4</h4>\n", ElephantMarkdown::parse("#### This is an H4")
        );
        $this->assertEquals(
            "<h4>This is an H4</h4>\n", ElephantMarkdown::parse("#### This is an H4#")
        );
    }

    public function testH5Atx()
    {
        $this->assertEquals(
            "<h5>This is an H5</h5>\n", ElephantMarkdown::parse("##### This is an H5")
        );
        $this->assertEquals(
            "<h5>This is an H5</h5>\n", ElephantMarkdown::parse("##### This is an H5#")
        );
    }

    public function testH6Atx()
    {
        $this->assertEquals(
            "<h6>This is an H6</h6>\n", ElephantMarkdown::parse("###### This is an H6")
        );
        $this->assertEquals(
            "<h6>This is an H6</h6>\n", ElephantMarkdown::parse("###### This is an H6#")
        );
    }

    public function testHAtxCloseMismatch()
    {
        $this->assertEquals(
            "<h6>Outro Teste</h6>\n", ElephantMarkdown::parse("###### Outro Teste ##")
        );
    }

    public function testSimpleBlockquotes()
    {
        $this->assertEquals(<<<HTML
<blockquote>
  <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
  consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
  Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
  
  <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
  id sem consectetuer libero luctus adipiscing.</p>
</blockquote>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
> consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
> Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.
> 
> Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
> id sem consectetuer libero luctus adipiscing.
MD
        ));
    }

    public function testLazyBlockquotes()
    {
        $this->assertEquals(<<<HTML
<blockquote>
  <p>This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
  consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
  Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.</p>
  
  <p>Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
  id sem consectetuer libero luctus adipiscing.</p>
</blockquote>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
> This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae, risus.

> Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse
id sem consectetuer libero luctus adipiscing.
MD
        ));
    }

    public function testNestedBlockquotes()
    {
        $this->assertEquals(<<<HTML
<blockquote>
  <p>This is the first level of quoting.</p>
  
  <blockquote>
    <p>This is nested blockquote.</p>
  </blockquote>
  
  <p>Back to the first level.</p>
</blockquote>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
> This is the first level of quoting.
>
> > This is nested blockquote.
>
> Back to the first level.
MD
        ));
    }

    public function testNestedRickBlockquotes()
    {
        $this->assertEquals(<<<HTML
<blockquote>
  <h2>This is a header.</h2>
  
  <ol>
  <li>This is the first list item.</li>
  <li>This is the second list item.</li>
  </ol>
  
  <p>Here's some example code:</p>

<pre><code>return shell_exec("echo \$input | \$markdown_script");
</code></pre>
</blockquote>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
> ## This is a header.
> 
> 1.   This is the first list item.
> 2.   This is the second list item.
> 
> Here's some example code:
> 
>     return shell_exec("echo \$input | \$markdown_script");
MD
        ));
    }

    public function testSimpleLists()
    {
        $this->assertEquals(<<<HTML
<ul>
<li>Red</li>
<li>Blue</li>
<li>Green</li>
</ul>

HTML
            , ElephantMarkdown::parse(
                <<<MD
- Red
- Blue
- Green
MD
        ));
    }

    public function testSimpleListsPlusSign()
    {
        $this->assertEquals(<<<HTML
<ul>
<li>Foo</li>
<li>Bar</li>
<li>Baz</li>
</ul>

HTML
            , ElephantMarkdown::parse(
                <<<MD
+ Foo
+ Bar
+ Baz
MD
        ));
    }

    public function testSimpleListsAsterisk()
    {
        $this->assertEquals(<<<HTML
<ul>
<li>Foo</li>
<li>Bar</li>
<li>Baz</li>
</ul>

HTML
            , ElephantMarkdown::parse(
                <<<MD
* Foo
* Bar
* Baz
MD
        ));
    }

    public function testSimpleListsIndented()
    {
        $this->assertEquals(<<<HTML
<ul>
<li>Foo something, bar something
lorem ipsum etc</li>
<li>Bar everything, bar something
lorem ipsum etc</li>
<li>Bat Man, bar something
lorem ipsum etc</li>
</ul>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
*  Foo something, bar something
   lorem ipsum etc
*  Bar everything, bar something
   lorem ipsum etc
*  Bat Man, bar something
   lorem ipsum etc
MD
        ));
    }

    public function testSimpleListsParagraph()
    {
        $this->assertEquals(<<<HTML
<ul>
<li><p>Foo</p></li>
<li><p>Bar</p></li>
<li><p>Baz</p></li>
</ul>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
* Foo
    
* Bar

* Baz
MD
        ));
    }

    public function testBlocksInsideLists()
    {
        $this->assertEquals(<<<HTML
<ul>
<li><p>Foo</p>

<pre><code>sudo make me a sandwich
</code></pre></li>
<li><p>Bar</p>

<blockquote>
  <p>Cool.</p>
</blockquote></li>
</ul>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
*   Foo
    
        sudo make me a sandwich
        
*   Bar

    >Cool.
MD
        ));
    }

    public function testListsMultipleParagraph()
    {
        $this->assertEquals(<<<HTML
<ul>
<li><p>Foo</p>

<p>Second Foo</p></li>
<li><p>Bar</p>

<p>Second Bar</p></li>
<li><p>Baz</p>

<p>Second Baz</p></li>
</ul>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
*   Foo
    
    Second Foo
    
*   Bar

    Second Bar

*   Baz
    
    Second Baz
MD
        ));
    }

    public function testOrderedLists()
    {
        $this->assertEquals(<<<HTML
<ol>
<li>Foo</li>
<li>Bar</li>
<li>Baz</li>
</ol>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
1. Foo
2. Bar
3. Baz
MD
        ));
    }

    public function testListEscapedDot()
    {
        $this->assertEquals(<<<HTML
<p>1990&#46; Nice Year
1991&#46; Terrible Year.</p>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
1990\. Nice Year
1991\. Terrible Year.
MD
        ));
    }

    public function testOrderedListsCustom()
    {
        $this->assertEquals(<<<HTML
<ol>
<li>Foo</li>
<li>Bar</li>
<li>Baz</li>
</ol>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
1. Foo
8. Bar
5. Baz
MD
        ));
    }

    public function testNestedLists()
    {
        $this->assertEquals(<<<HTML
<ol>
<li>Foo

<ul>
<li><em>Foo bat</em></li>
<li>Foo foo</li>
</ul></li>
<li>Bar</li>
<li>Baz</li>
</ol>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
1. Foo
- *Foo bat*
- Foo foo
2. Bar
3. Baz
MD
        ));
    }

    public function testCodeBlock()
    {
        $this->assertEquals(<<<HTML
<pre><code>Some
Geeky &lt;strong&gt;HTML-escaped&lt;/strong&gt;
Code
</code></pre>

HTML
            ,
            ElephantMarkdown::parse(
                <<<MD
    Some
    Geeky <strong>HTML-escaped</strong>
    Code
MD
        ));
    }

    public function testHorizontal()
    {
        $this->assertEquals(<<<HTML
<hr />

HTML
            , ElephantMarkdown::parse(
                <<<MD
***********
MD
        ));
    }

    public function testHorizontal2()
    {
        $this->assertEquals(<<<HTML
<hr />

HTML
            , ElephantMarkdown::parse(
                <<<MD
- - - -
MD
        ));
    }

    public function testHorizontal3()
    {
        $this->assertEquals(<<<HTML
<hr />

HTML
            , ElephantMarkdown::parse(
                <<<MD
* * * * 
MD
        ));
    }

    public function testSimpleLinks()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Title">an example</a> inline link.</p>

<p><a href="http://example.net/">This link</a> has no title attribute.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example](http://example.com/ "Title") inline link.

[This link](http://example.net/) has no title attribute.
MD
        ));
    }

    public function testLinksRelative()
    {
        $this->assertEquals(<<<HTML
<p>See my <a href="/about/">About</a> page for details.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
See my [About](/about/) page for details.
MD
        ));
    }

    public function testReferences()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example][id] reference-style link.

[id]: http://example.com/  "Optional Title Here"
MD
        ));
    }

    public function testReferencesSpaced()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example] [id] reference-style link.

[id]: http://example.com/  "Optional Title Here"
MD
        ));
    }

    public function testReferencesSpacedSingleQuotes()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example] [id] reference-style link.

[id]: http://example.com/  'Optional Title Here'
MD
        ));
    }

    public function testReferencesSpacedParenthesis()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example] [id] reference-style link.

[id]: http://example.com/  (Optional Title Here)
MD
        ));
    }

    public function testReferencesLines()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/longish/path/to/resource/here" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example] [id] reference-style link.
    
[id]: http://example.com/longish/path/to/resource/here
    "Optional Title Here"
MD
        ));
    }

    public function testReferencesCaseSensitivity()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example] [ID] reference-style link.

[id]: http://example.com/  (Optional Title Here)
MD
        ));
    }

    public function testReferencesImplicit()
    {
        $this->assertEquals(<<<HTML
<p>This is <a href="http://example.com/" title="Optional Title Here">an example</a> reference-style link.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
This is [an example][] reference-style link.

[an example]: http://example.com/  (Optional Title Here)
MD
        ));
    }

    public function testReferencesFullSample()
    {
        $this->assertEquals(<<<HTML
<p>I get 10 times more traffic from <a href="http://google.com/" title="Google">Google</a> than from
<a href="http://search.yahoo.com/" title="Yahoo Search">Yahoo</a> or <a href="http://search.msn.com/" title="MSN Search">MSN</a>.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
I get 10 times more traffic from [Google] [1] than from
[Yahoo] [2] or [MSN] [3].

  [1]: http://google.com/        "Google"
  [2]: http://search.yahoo.com/  "Yahoo Search"
  [3]: http://search.msn.com/    "MSN Search"
MD
        ));
    }

    public function testReferencesFullSampleImplicit()
    {
        $this->assertEquals(<<<HTML
<p>I get 10 times more traffic from <a href="http://google.com/" title="Google">Google</a> than from
<a href="http://search.yahoo.com/" title="Yahoo Search">Yahoo</a> or <a href="http://search.msn.com/" title="MSN Search">MSN</a>.</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
I get 10 times more traffic from [Google][] than from
[Yahoo][] or [MSN][].

  [google]: http://google.com/        "Google"
  [yahoo]:  http://search.yahoo.com/  "Yahoo Search"
  [msn]:    http://search.msn.com/    "MSN Search"
MD
        ));
    }

    public function testEmphasisSingleAsterisks()
    {
        $this->assertEquals(<<<HTML
<p><em>single asteriscs</em></p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
*single asteriscs*
MD
        ));
    }

    public function testEmphasisSingleunderscores()
    {
        $this->assertEquals(<<<HTML
<p><em>single underscores</em></p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
_single underscores_
MD
        ));
    }

    public function testEmphasisDoubleAsterisks()
    {
        $this->assertEquals(<<<HTML
<p><strong>double asteriscs</strong></p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
**double asteriscs**
MD
        ));
    }

    public function testEmphasisDoubleunderscores()
    {
        $this->assertEquals(<<<HTML
<p><strong>double underscores</strong></p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
__double underscores__
MD
        ));
    }

    public function testEmphasisSingleAsterisksWord()
    {
        $this->assertEquals(<<<HTML
<p>un<em>frigging</em>believable</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
un*frigging*believable
MD
        ));
    }

    public function testEmphasisSingleunderscoresWord()
    {
        $this->assertEquals(<<<HTML
<p>un_frigging_believable</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
un_frigging_believable
MD
));
    }

    public function testEmphasisDoubleAsterisksWord()
    {
        $this->assertEquals(<<<HTML
<p>un<strong>frigging</strong>believable</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
un**frigging**believable
MD
        ));
    }

    public function testEmphasisDoubleunderscoresWord()
    {
        $this->assertEquals(<<<HTML
<p>un__frigging__believable</p>

HTML
            , ElephantMarkdown::parse(
                <<<MD
un__frigging__believable
MD
));
    }

}