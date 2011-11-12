<?php
/**
 * Copyright (C) 2011, Maxim S. Tsepkov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once __DIR__ . '/../Filter.php';

/**
 * This filter spawns original Markdown perl script as a subprocess,
 * pass text to its stdin and then return its stdout.
 *
 * This is used as fallback option to make library usable
 * until native PHP support is implemented.
 *
 * Output of this filter is canonical, PHP filters must produce equal output.
 *
 * @package markdown-oo-php
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me> http://www.garygolden.me
 * @link http://daringfireball.net/projects/markdown/
 * @version 0.9
 *
 */
class Markdown_Filter_Fallback extends Markdown_Filter
{
    /**
     * Pass text through original Markdown perl script.
     *
     * @param string $text
     * @return string
     * @throws RuntimeException
     */
    public function transform($text)
    {
#		$cmdline = 'perl ' . __DIR__ . '/Fallback/Markdown.pl';
        $cmdline = '/usr/local/bin/multimarkdown';
        $child = proc_open(
            $cmdline,
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            ),
            $pipes
        );

        if (is_resource($child)) {
            fwrite($pipes[0], $text);
            fclose($pipes[0]);

            $text = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $returnCode = proc_close($child);
            if ($returnCode > 0) {
                throw new RuntimeException(
                    'Child process exited with status ' . $returnCode . PHP_EOL
                    . 'STDERR: ' . $err
                );
            }
        }
        else {
            throw RuntimeException('Could not create process.');
        }

        return $text;
    }
}
