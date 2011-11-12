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

class Markdown_Filter_CustomBlock extends Markdown_Filter {
	public function transform($text) {
		$regex = '/
			(?>
				(^[ ]*\.\.[ ]?			# ">" at the start of a line
				(.+)::\n)				# rest of the first line
				((.+\n)*)					# subsequent consecutive lines
			)+
			/xm';
		$text = preg_replace_callback($regex,
				array(&$this, 'preg_replace'), $text);
				
		return $text;
	}
	
	public function preg_replace($matches){
		$blockClass = sprintf("Markdown_Block_%s", ucfirst($matches[2]));
		$blockFile = __DIR__ . sprintf('/../Block/%s.php', ucfirst($matches[2]));
		if(file_exists($blockFile)){
			include_once($blockFile);
			if(class_exists($blockClass)){
				$block = new $blockClass();
				return $block->transform($matches[3]);
			}
		}
		
		return $matches[0];
	}
}
