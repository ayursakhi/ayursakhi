<?php
namespace Codazon\ThemeOptions\Framework\View\Asset\Source;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Plugin
{
    public function __construct(
        //\Codazon\ThemeOptions\Framework\App\Config\Initial $initialConfig,
        \Codazon\ThemeOptions\Model\Config\Reader\Store $storeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Codazon\ThemeOptions\Helper\Data $helper
    ) {
        $this->state = $state;
        $this->helper = $helper;
        $this->design = $design;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        $this->themeFactory = $themeFactory;
        $this->connection = $connection;
        //print_r($this->config);die;
    }

    public function loadData($asset)
    {

        $path = 'frontend/'.$asset->getContext()->getThemePath();
        $theme = $this->themeFactory->create()->getThemeByFullPath($path);
        $conn = $this->connection->getConnection();
        $tblConfig = $this->connection->getTableName('design_config_grid_flat');
        if($theme->getThemeId()){
            $q = 'SELECT store_id FROM `'.$tblConfig.'` WHERE theme_theme_id='.$theme->getThemeId() .' ORDER BY store_id ASC';
            $data = $conn->fetchAll($q);
        }else{
            $data = array();
        }
        if(isset($data[0])){
            foreach($data as $row){
                if($row['store_id']){
                    $storeId = $row['store_id'];
                    break;
                }
            }
        }else{
            $storeId = $this->storeManager->getStore()->getId();
        }
        if(empty($storeId)){
            $this->config = array();
            $this->data = array();
        }else{
            $this->config = $this->storeConfig->read($storeId);
            $this->data = array();
        }
    }

    public function getPath($data, $path = null)
    {
        if(!is_array($data)){
            $this->data[$path] = $data;
        }else{
            foreach($data as $key => $value){
                $this->getPath($value, $key);
                /*if($path){
                    $this->getPath($value, $path.'/'.$key);
                }else{
                    $this->getPath($value, $key);
                }*/
            }
        }
    }
    public function aroundGetContent($subject, $procede, $asset)
    {
        $path = $asset->getPath();
        $result = $procede($asset);     
        //echo "<pre>";print_r($this->data);die;
        if (strpos($path, 'frontend/Codazon') !== false && strpos($path, '.less') !== false) {
            if (strpos($path,'source/_variables.less') !== false) {
                $this->loadData($asset);
                $data = $this->config;
                if(isset($this->config['variables'])){
                    $data = $this->config['variables'];
                }
                $this->getPath($data);
                //unset($this->data[0]);
                foreach($this->data as $key => $value){
                    //$var = str_replace('/','-',$key);
                    //$result .= '@'.$key.":~'".$value."'; ";               
                    if (strpos($value, '#') !== false) {
                        $result .= '@'.$key.':'.$value.'; ';        
                    } elseif(strpos($value, 'rgba(') !== false){
                        $result .= '@'.$key.':'.$value.'; ';
                    }else{
                        
                        if(preg_match("/background_file/",$key))
                        {                                       
                            if($value){
                                $background_url = $this->helper->getBaseUrl() .'pub/media/codazon/themeoptions/background/'. $value;
                                $result .= '@'.$key.":~'".$background_url."'; ";
                            }else{
                                $value = '../images/transparent.png';
                                $result .= '@'.$key.":~'".$value."'; ";
                            }
                        }
                        else
                            $result .= '@'.$key.":~'".$value."'; ";
                    }
                }
            }
        }
        return $result;
    }
}