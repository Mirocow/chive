<?php

/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 */
class LinkPager extends CLinkPager
{
	const CSS_FIRST_ELEMENT = 'first-element';
	const CSS_LAST_ELEMENT = 'last-element';
	const CSS_SETTINGS = 'settings';

	public static $generateJsPage = true;
	public static $generateJsPageSize = true;
	
	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		if($this->nextPageLabel === null)
		{
			$this->nextPageLabel = '&raquo;';
		}
		if($this->prevPageLabel === null)
		{
			$this->prevPageLabel = '&laquo;';
		}
		if($this->firstPageLabel === null)
		{
			$this->firstPageLabel = Yii::t('core', 'first');
		}
		if($this->lastPageLabel === null)
		{
			$this->lastPageLabel = Yii::t('core', 'last');
		}
		$this->maxButtonCount = 5;

		$buttons = $this->createPageButtons();

		if(empty($buttons))
		{
			return;
		}

		$htmlOptions = $this->htmlOptions;
		if(!isset($htmlOptions['id']))
		{
			$htmlOptions['id'] = $this->getId();
		}
		if(!isset($htmlOptions['class']))
		{
			$htmlOptions['class'] = 'yiiPager';
		}
		echo $this->header;
		echo CHtml::tag('ul', $htmlOptions, implode("\n", $buttons));
		echo $this->footer;
	}

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons()
	{
		list($beginPage, $endPage) = $this->getPageRange();
		$currentPage = $this->getCurrentPage(false); // currentPage is calculated in getPageRange()
		$buttons = array();
		$pageCount = $this->getPageCount();

		// first page
		if($beginPage > 0)
		{
			$buttons[] = $this->createPageButton(
				$this->firstPageLabel,
				0,
				self::CSS_FIRST_PAGE . ' ' . self::CSS_FIRST_ELEMENT,
				$beginPage <= 0,
				false);
		}

		// prev page
		if(($page = $currentPage - 1) < 0)
		{
			$page = 0;
		}
		$buttons[] = $this->createPageButton(
			$this->prevPageLabel,
			$page,
			self::CSS_PREVIOUS_PAGE . ($beginPage == 0 ? ' ' . self::CSS_FIRST_ELEMENT : null),
			$currentPage <= 0,
			false);

		// internal pages
		for($i = $beginPage; $i <= $endPage; ++$i)
		{
			$buttons[] = $this->createPageButton(
				$i + 1,
				$i,
				self::CSS_INTERNAL_PAGE,
				false,
				$i == $currentPage);
		}

		// next page
		if(($page = $currentPage + 1) >= $pageCount - 1)
		{
			$page = $pageCount - 1;
		}
		$buttons[] = $this->createPageButton(
			$this->nextPageLabel,
			$page,
			self::CSS_NEXT_PAGE,
			$currentPage >= $pageCount - 1,
			false);

		// last page
		if($endPage < $pageCount - 1)
		{
			$buttons[] = $this->createPageButton(
				$this->lastPageLabel,
				$pageCount - 1,
				self::CSS_LAST_PAGE,
				$endPage >= $pageCount - 1,
				false);
		}

		// settings
		$content = '';
		$sizes = array(5, 10, 50, 100, 500);
		$currentSize = $this->getPageSize();
		foreach($sizes AS $size)
		{
			if($size == $currentSize)
			{
				$content .= '&nbsp;' . $size . '&nbsp;';
			}
			elseif($this->getPages()->postVars !== null)
			{
				if(self::$generateJsPageSize)
				{
					$data = json_encode($this->getPages()->postVars);
					
					$script = '
						function setPageSize(_pageSize) {
						
							var data = ' . $data . ';
							data.pageSize = _pageSize; 
						
							$.post("'.BASEURL . '/' . $this->getPages()->route.'", data, function(responseText) {
								$("div.ui-layout-center").html(responseText);
								init();
							});
						
						}
					';
					
					Yii::app()->getClientScript()->registerScript('LinkPager_pageSize', $script);
					
					self::$generateJsPageSize = false;
				}
				
				$content .= '&nbsp;' . CHtml::link($size, 'javascript:void(0)', array(
					'onclick' => 'setPageSize(' . $size . ');',
				));
				
			}
			else
			{
				$content .= '&nbsp;<a href="' . $this->createPageUrl($this->getCurrentPage(), $size) . '">' . $size . '</a>&nbsp;';
			}
		}
		$icon = new Icon();
		$icon->text = 'core.settings';
		$icon->name = 'operation';
		$icon->size = 12;
		$buttons[] =
			'<li class="' . self::CSS_SETTINGS . ' ' . self::CSS_LAST_ELEMENT . '">'
				. '<a href="javascript:void(0)" '
					. 'onclick="$(this).parent().hide().next().show()">' . Yii::t('core', 'entriesPerPage') . ':&nbsp;&nbsp;' . $currentSize . '</a>'
			. '</li>'
			. '<li class="' . self::CSS_SETTINGS . ' ' . self::CSS_LAST_ELEMENT . '" style="display: none">'
				. '<span>' . Yii::t('core', 'entriesPerPage') . ':&nbsp;&nbsp;' . $content . '</span>'
			. '</li>';

		return $buttons;
	}

	/**
	 * @see CBasePager::createPageUrl()
	 */
	protected function createPageUrl($page, $pageSize = null)
	{
		return $this->getPages()->createPageUrl($this->getController(), $page, $pageSize);
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * @param string the text label for the button
	 * @param integer the page number
	 * @param string the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean whether this page button is visible
	 * @param boolean whether this page button is selected
	 * @return string the generated button
	 */
	protected function createPageButton($label,$page,$class,$hidden,$selected)
	{
		if($hidden || $selected)
			$class.=' '.($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);
			
		$postVars = $this->getPages()->postVars;	
			
		if($postVars === null)
		{
			return '<li class="'.$class.'">'.CHtml::link($label,$this->createPageUrl($page)).'</li>';
		}
		else
		{
			
			if(self::$generateJsPage)
			{
				
				$data = json_encode($postVars);
				
				$script = '
					function navigateToPage(_page) {
					
						var data = ' . $data . ';
						data.page = _page; 
					
						$.post("'.BASEURL . '/' . $this->getPages()->route.'", data, function(responseText) {
							$("div.ui-layout-center").html(responseText);
							init();
						});
					
					}
				';
				
				
				Yii::app()->getClientScript()->registerScript('LinkPager_page', $script);
				
				self::$generateJsPage = false;
			}
			
			return '<li class="'.$class.'">'.CHtml::link($label,'javascript:void(0);', array(
				'onclick'=>'navigateToPage(' . ($page + 1) . ');'
			)).'</li>';
		}
			
	}
	
}
