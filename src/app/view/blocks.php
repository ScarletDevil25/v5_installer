<?php
namespace View;

class Blocks extends Base
{
	
	public static function pageMenu($main, $sub, $vertical = FALSE)
	{
		\Base::instance()->set('menuMain', $main);
		\Base::instance()->set('menuSub', $sub);
		
		if ( $vertical )
			return parent::render('blocks/menu.vert.html');
		
		else
			return parent::render('blocks/menu.html');
	}

	public static function shoutboxInit()
	{
		\Registry::get('VIEW')->javascript( 'head', TRUE, "shoutbox.js" );
		return parent::render('blocks/shoutbox.html');
	}

	public static function shoutboxLines($data)
	{
		\Base::instance()->set('shoutboxLines', $data);
		return parent::render('blocks/shoutbox.inner.html');
	}

	public static function shoutboxForm()
	{
		if ( $_SESSION['userID']==0 )
			\Base::instance()->set('shoutboxGuest', TRUE);
		else
			\Base::instance()->set('shoutboxMember', TRUE);
		
		return parent::render('blocks/shoutbox.inner.html');
	}
	
	public static function calendarInit()
	{
		\Registry::get('VIEW')->javascript( 'body', TRUE, "calendar.js.php?base=".\Base::instance()->get('BASE') );
		
		$cell = array ( 
				"ID"		=> "sb_cell_calendar",
				"TITLE"		=> "__Calendar",
				"CONTENT"	=> "Loading ...",
		);
		\Base::instance()->set('cell', $cell);
		return parent::render('blocks/cell.html');
	}

	public static function calendar($data)
	{
		list($events, $c, $start) = $data;
		
		$day_count = date("t",mktime(0,0,0,$c['month'],1,$c['year']));
		$blanks_front = ( \Config::getPublic('monday_first_day') == 1 ) ? date('N',mktime(0,0,0,$c['month'],1,$c['year']))-1 : date('w',mktime(0,0,0,$c['month'],1,$c['year'])) ;
		$rows_required = intval ( ($day_count+$blanks_front+6) / 7 );
		$blanks_after = $rows_required*7 - $blanks_front - $day_count;

		$now 	 = array ( "month"	=> date("n"),
						 "year"		=> date("Y") );
		/*
			check if we have events on prior calendar sheets
		*/
		$back = ( ($c['year'] > $start['year']) || ($c['year']==$start['year'] && $c['month'] > $start['month']) )
			? date("Y-m",mktime(0,0,0,$c['month']-1,1,$c['year']))
			: FALSE;

		/*
				check if we have events on later calendar sheets
		*/
		$forward = ( ($c['year'] < $now['year']) || ($c['year']==$now['year'] && $c['month'] < $now['month']) )
		? date("Y-m",mktime(0,0,0,$c['month']+1,1,$c['year']))
		: FALSE;
		
		$today = ($c['year']==$now['year'] && $c['month']==$now['month']) ? FALSE : date("Y-m");

		// create empty leading cells
		for ( $i=1; $i <= $blanks_front; $i++ )
		{
			$cells[] = [ FALSE ];
		}

		// create days
		for ( $i=1; $i <= $day_count; $i++ )
		{
			$cells[] = array (
								"LINK"	=>	( isset($events[$i]) ) ? "{$c['year']}-{$c['month']}-{$i}" : FALSE,
								"I"			=>	$i, 
							);
		}
		
		// create empty tailing cells
		for ( $i=1; $i <= $blanks_after; $i++ )
		{
			$cells[] = [ FALSE ];
		}
		
		setlocale(LC_ALL, __transLocale);
		
		$data = [
			"CELLS"		=>	$cells,
			"BACK"		=>	$back,
			"TODAY"		=>	$today,
			"FORWARD"	=>	$forward,
			"MONTH"		=>	$c['month'],
			"YEAR"		=>	$c['year'],
			"TITLE"		=>	mktime(0,0,0,$c['month'],1,$c['year']),
			"TITLELINK" =>	$events===FALSE ? FALSE : "{$c['year']}-{$c['month']}",
		];

		\Base::instance()->set('data', $data);

		return utf8_encode(parent::render('blocks/calendar.html'));
	}
	
	public static function categories($data)
	{
		
		$cell = array ( 
				"ID"		=> "sb_cell_categories",
				"TITLE"		=> \Base::instance()->get("LN__Categories"),
				"CONTENT"	=> $data,
		);
		\Base::instance()->set('cell', $cell);
		return parent::render('blocks/categories.html');
	}
}
