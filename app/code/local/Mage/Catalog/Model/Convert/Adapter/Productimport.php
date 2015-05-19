<?php
/**
* Product_import.php
* 
* @copyright  copyright (c) 2009 toniyecla[at]gmail.com
* @license    http://opensource.org/licenses/osl-3.0.php open software license (OSL 3.0)
*/

class Mage_Catalog_Model_Convert_Adapter_Productimport
extends Mage_Catalog_Model_Convert_Adapter_Product
 {
    
    /**
    * Save product (import)
    * 
    * @param array $importData 
    * @throws Mage_Core_Exception
    * @return bool 
    */
    public function saveRow( array $importData )
    
    {
      // echo "<pre>";
      // print_r($importData);//exit;
        $product = $this -> getProductModel();
        $product -> setData( array() );
        
       // if ( $stockItem = $product -> getStockItem() ) {
        //    $stockItem -> setData(array());
       //     } 
            //echo $importData['tax_class_id'];exit;
            
        //echo "----".$extendedattribute;exit;
        if ( empty( $importData['store'] ) ) {
            if ( !is_null( $this -> getBatchParams( 'store' ) ) ) {
                $store = $this -> getStoreById( $this -> getBatchParams( 'store' ) );
                } else {
                $message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "%s" not defined', 'store' );
                Mage :: throwException( $message );
                } 
            } else {
            $store = $this -> getStoreByCode( $importData['store'] );
            } 
        
        if ( $store === false ) {
            $message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, store "%s" field not exists', $importData['store'] );
            Mage :: throwException( $message );
            } 
        
        if ( empty( $importData['sku'] ) ) {
            $message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "%s" not defined', 'sku' );
            Mage :: throwException( $message );
            } 
        
        $product -> setStoreId( $store -> getId() );
        $productId = $product -> getIdBySku( $importData['sku'] );
        $new = true; // fix for duplicating attributes error
        if ( $productId ) {
            $product -> load( $productId );
            $product=Mage::getModel('catalog/product')->load($productId);
            $new = false; // fix for duplicating attributes error
            } 
            if ( isset( $importData['extended_attribute'] ) ) {
            $importData['extended_attribute']=str_replace('_',' ',$importData['extended_attribute']);            
             $extended_attribute=$importData['extended_attribute'];
             $extendedarray=explode(',',$extended_attribute);
            $extendedattribute="<table  class='data-table'>";
            foreach ($extendedarray as $array) {
                $extendedattribute.="<tr>";
                $extended_array=explode('|',$array);
                $i=1;
                foreach ($extended_array as $arr) {
                    $extendedattribute.="<td class='extendedAttributeC".$i."'>".trim($arr)."</td>";
                    $i++;
                }
                $extendedattribute.="</tr>";
            }
            $extendedattribute.="</table>";
            $importData['extended_attribute']=$extendedattribute;
            $product -> setExtendedAttribute($extendedattribute);
             
        }

         if ( isset( $importData['name'] ) ) {
             
            $product -> setName(trim($importData['name']));
             
        }
        
        if ( isset( $importData['type'] ) ) {
        $product -> setTypeId(trim(strtolower( $importData['type']) ) );
    }
        $product -> save();
       // print_r($product);exit;
        return true;
        } 
    
    protected function userCSVDataAsArray( $data )
    
    {
        return explode( ',', str_replace( " ", "", $data ) );
        } 
    
    protected function skusToIds( $userData, $product )
    
    {
        $productIds = array();
        foreach ( $this -> userCSVDataAsArray( $userData ) as $oneSku ) {
            if ( ( $a_sku = ( int )$product -> getIdBySku( $oneSku ) ) > 0 ) {
                parse_str( "position=", $productIds[$a_sku] );
                } 
            } 
        return $productIds;
        } 
    
    protected $_categoryCache = array();
    
    protected function _addCategories( $categories, $store )
    
    {
        // $rootId = $store->getRootCategoryId();
        // $rootId = Mage::app()->getStore()->getRootCategoryId();
        $rootId = 2; // our store's root category id
        if ( !$rootId ) {
            return array();
            } 
        $rootPath = '1/' . $rootId;
        if ( empty( $this -> _categoryCache[$store -> getId()] ) ) {
            $collection = Mage :: getModel( 'catalog/category' ) -> getCollection()
             -> setStore( $store )
             -> addAttributeToSelect( 'name' );
            $collection -> getSelect() -> where( "path like '" . $rootPath . "/%'" );
            
            foreach ( $collection as $cat ) {
                try {
                    $pathArr = explode( '/', $cat -> getPath() );
                    $namePath = '';
                    for ( $i = 2, $l = sizeof( $pathArr ); $i < $l; $i++ ) {
                        $name = $collection -> getItemById( $pathArr[$i] ) -> getName();
                        $namePath .= ( empty( $namePath ) ? '' : '/' ) . trim( $name );
                        } 
                    $cat -> setNamePath( $namePath );
                    } 
                catch ( Exception $e ) {
                    echo "ERROR: Cat - ";
                    print_r( $cat );
                    continue;
                    } 
                } 
            
            $cache = array();
            foreach ( $collection as $cat ) {
                $cache[strtolower( $cat -> getNamePath() )] = $cat;
                $cat -> unsNamePath();
                } 
            $this -> _categoryCache[$store -> getId()] = $cache;
            } 
        $cache = &$this -> _categoryCache[$store -> getId()];
        
        $catIds = array();
        foreach ( explode( ',', $categories ) as $categoryPathStr ) {
            $categoryPathStr = preg_replace( '#s*/s*#', '/', trim( $categoryPathStr ) );
            if ( !empty( $cache[$categoryPathStr] ) ) {
                $catIds[] = $cache[$categoryPathStr] -> getId();
                continue;
                } 
            $path = $rootPath;
            $namePath = '';
            foreach ( explode( '/', $categoryPathStr ) as $catName ) {
                $namePath .= ( empty( $namePath ) ? '' : '/' ) . strtolower( $catName );
                if ( empty( $cache[$namePath] ) ) {
                    $cat = Mage :: getModel( 'catalog/category' )
                     -> setStoreId( $store -> getId() )
                     -> setPath( $path )
                     -> setName( $catName )
                     -> setIsActive( 1 )
                     -> save();
                    $cache[$namePath] = $cat;
                    } 
                $catId = $cache[$namePath] -> getId();
                $path .= '/' . $catId;
                } 
            if ( $catId ) {
                $catIds[] = $catId;
                } 
            } 
        return join( ',', $catIds );
        } 
    
    protected function _removeFile( $file )
    
    {
        if ( file_exists( $file ) ) {
            if ( unlink( $file ) ) {
                return true;
                } 
            } 
        return false;
        } 
    }
    
