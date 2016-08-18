<?php
/*
   Question2Answer by Gideon Greenspan and contributors
   http://www.question2answer.org/

   File: qa-plugin/example-page/qa-example-page.php
   Description: Page module class for example page plugin


   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   More about this license: http://www.question2answer.org/license.php
 */

class qa_quote_page
{
	private $directory;
	private $urltoroot;
	private $new;

	public function load_module($directory, $urltoroot)
	{
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}


	public function suggest_requests() // for display in admin interface
	{
		return array(
				array(
					'title' => 'Quote',
					'request' => 'quote-plugin-page',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				     ),
			    );
	}

	public function init_queries($tableslc)
	{
		$queries = array();
		$tablename=qa_db_add_table_prefix('quotes');
		if(!in_array($tablename, $tableslc)) {
			$new = true;
			$queries[] = "CREATE TABLE IF NOT EXISTS `".$tablename."` (
				`quoteid` int(10) unsigned auto_increment primary key,
				`quote` varchar(3072) unique
					)
					";
			qa_opt("quotesaved", "false");
		}
		$hour = date("G");
		if($hour == "0")
		{
			if(qa_opt("quotesaved")==="false")//want to tun this only once a day
			{
				$query = "select quote from ^quotes order by rand() limit 1";
				$result = qa_db_query_sub($query);
				$quote = qa_db_read_one_value($result);
				qa_opt("quoteod", $quote);
				qa_opt("quotesaved", "true");
			}
		}
		else {
			if(qa_opt("quotesaved")==="true")
				qa_opt("quotesaved", "false");
		}
		return $queries;

	}
	public function match_request($request)
	{
		return $request == 'quote-plugin-page';
	}


	public function process_request($request)
	{
		$qa_content=qa_content_prepare();

		$qa_content['title']=qa_lang_html('quote_page/page_title');
		if(qa_clicked('okthen'))
		{
			$insert = "insert into ^quotes (quote) values ($)";
			qa_db_query_sub($insert, qa_post_text('quote'));
		}

		$qa_content['form']=array(
				'tags' => 'method="post" action="'.qa_self_html().'"',

				'style' => 'wide',

				'ok' => qa_post_text('okthen') ? 'Quote saved' : null,

				'title' => 'Input a quote',

				'fields' => array(
					'request' => array(
						'label' => 'Quote',
						'tags' => 'name="quote"',
						'type' => 'textarea',
						'rows' => 20,
						'value' => '',
						),

					),

				'buttons' => array(
					'ok' => array(
						'tags' => 'name="okthen"',
						'label' => 'Submit',
						'value' => '1',
						),
					),

				'hidden' => array(
						'hiddenfield' => '1',
						),
				);


		return $qa_content;
	}
}
