<?php
namespace Codazon\MegaMenu\Block\Adminhtml\Index;
class View extends \Magento\Backend\Block\Template
{
	protected $_filterProvider;
	protected $_storeManager;
	protected $_blockFactory;
	protected $_menuOject;
	protected $_blockFilter;
	
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $context->getStoreManager();
        $this->_blockFactory = $blockFactory;
		$storeId = $this->_storeManager->getStore()->getId();
		$this->_blockFilter = $this->_filterProvider->getBlockFilter()->setStoreId($storeId);
    }

	public function getMenuObject(){
		if(!$this->_menuOject){
			$this->_menuOject = new \Magento\Framework\DataObject($this->getMenu());
		}
		return $this->_menuOject;
	}
	protected function _toHtml()
    {
        return $this->filter(parent::_toHtml());
    }
	public function getParentType($items,$i){
		$k = $i - 1;
		while( isset($items[$k]) && ($k > 0)){
			if(($items[$k]->depth < $items[$i]->depth)){
				break;
			}else{
				$k = $k - 1;
			}
		}
		if(isset($items[$k])){
			return $items[$k]->item_type;
		}else{
			return false;	
		}
	}
	public function openTag($items,$i){
		$curDepth = $items[$i]->depth;
		$prevDepth = isset($items[$i-1])?$items[$i-1]->depth:$curDepth;
		if($curDepth == $prevDepth){
			$html = '<li';
		}elseif($curDepth > $prevDepth){
			if(isset($items[$i-1]) and 'col' == $this->getParentType($items,$i)){
				$html = '<ul class="groupmenu-nondrop"><li';
			}else{
				if($items[$i]->item_type == 'col'){
					$html = '<ul class="temp"><li';
				}else{
					$html = '<ul class="groupmenu-drop"><li';
				}			
			}
		}else{
			$html = '</li><li';
		}
		return $html;
	}
	public function closeTag($items,$i){
		$curDepth = $items[$i]->depth;
		$nextDepth = isset($items[$i+1])?$items[$i+1]->depth:0;
		if($curDepth == $nextDepth){
			$html = '</li>';
		}elseif($curDepth > $nextDepth){
			$html = str_repeat('</li></ul>',$curDepth-$nextDepth);
		}else{
			$html = '';
		}
		return $html;
	}
	
	public function getIcon($content){
		if(isset($content->icon_type) && $content->icon_type == 0){
			return ($content->icon_font)?'<i class="menu-icon fa fa-'.$content->icon_font.'"></i>':'';	
		}else{
			return ($content->icon_img)?'<i class="menu-icon img-icon"><img src="'.$content->icon_img.'"></i>':'';	
		}
	}
	
	public function filter($content){
		return $this->_blockFilter->filter($content);
	}
	
	public function hasChildren($items,$i){
		$curDepth = $items[$i]->depth;
		$nextDepth = isset($items[$i+1])?$items[$i+1]->depth:$curDepth;
		return ($nextDepth > $curDepth);
	}
	public function getBackgroundStyle($content){
		switch ($content->bg_position){
			case 'left_top':
				return "left:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
			case 'left_bottom':
				return "left:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
			case 'right_top':
				return "right:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
			case 'right_bottom':
			default:
				return "right:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
		}
	}
	public function getMenuContentArray(){
		if(!$this->_menuContentArray){
			$menu = $this->getMenuObject();
			$this->_menuContentArray = json_decode($menu->getContent());
		}
		return $this->_menuContentArray;
	}
	public function getItemCSSClass($items,$i)
	{
		$item = $items[$i];
		$depth = (int)$item->depth;
		$content = $item->content;

		$class[] = "item level{$depth} {$content->class}";
		if($depth == 0){
			$class[] = 'level-top';
		}
		switch ($item->item_type){
			case 'category':
				$class[] = 'parent cat-tree';
				if($content->display_type == 1){
					$class[] = 'no-dropdown';
				}
				break;
			case 'link':
				if($this->hasChildren($items,$i)){
					$class[] = 'parent';
				}
				break;
			case 'text':
				$class[] = 'text-content'; break;
			case 'row':
				$class[] = 'row no-dropdown'; break;
			case 'col':
				$class[] = 'col need-unwrap'; break;
			default:
		}
		return implode(' ',$class);
	}
}