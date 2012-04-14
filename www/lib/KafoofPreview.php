<?php
class KafoofPreview {
  protected $definition, $post;
  public function __construct($post,$definition=null) {
    if(!$definition && !empty($post['form-definition'])) $definition = $post['form-definition'];
    $this->definition = is_string($definition) ?  json_decode($definition) : $definition;
    $this->post = $post;
  }
  public function sections($post=null){
    if(!$post) $post = $this->post;
    if(!$this->definition) return "<p><b>Error:</b> Unable to render form preview.</p>";
    $def = unserialize(serialize($this->definition));
    $filled = array();
    $all = array_merge($def->routines,$def->subroutines);
    foreach($def->routines as $section) {
      $filled[] = $this->processSection($section,$post,$all);
    }
    return $filled;
  }
  protected function processSection($section,$context,$all) {
    $sout = new \stdClass();
    if(!empty($section->title)) $sout->title = $section->title;
    $sout->rows = array();
    $width = $section->width;
    foreach($section->rows as $i=>$row){
      $rrow = array_reverse($row);
      $r = array();
      $left = $width;
      foreach($rrow as $field){
        # TODO: branch for text, multi, include
        $f = new \stdClass();
        $f->width = sprintf('%.4F%%',$field->width/$width*100);
        if($field->left+$field->width < $left) {
          $f->pad = sprintf('%.4F%%',($left-$field->left-$field->width)/$width*100);
        }
        $left = $field->left;
        if('text'==$field->type) {
          $f->type = 'text';
          $f->value = strpos($field->value,'}}')===false ? $field->value : '';
        } elseif('import'==$field->type) {
          $values = $this->getValue($field->dataName,$context);
          $values = $values ? array($values) : array(array());
          $moreRows = $this->subSection($field->toImport,$values,$all);
          $sout->rows = array_merge($sout->rows,$moreRows[0]->rows);
          break;
        } elseif('include'==$field->type || 'multiinclude'==$field->type) {
          $f->type = 'include';
          $f->label = empty($field->label) ? null : $field->label;
          $values = $this->getValue($field->dataName,$context);
          if('include'==$field->type) $values = array($values);
          $f->sets = $this->subSection($field->toInclude,$values,$all);
        } else {
          $f->type = 'field';
          $f->label = $field->label;
          $f->value = $this->getValue($field->dataName,$context);
          if(is_array($f->value)) $f->value = implode(', ',$f->value);
        }
        $r[] = $f;
      }
      if($r){ // should always be $r, except for <<import>>
        if($row[0]->left > 0) {
          $f = new \stdClass();
          $f->type = 'space';
          $f->width = sprintf('%.4F%%',$row[0]->left/$width*100);
          $r[] = $f;
        }
        $r = array_reverse($r);
        $sout->rows[] = $r;
      }
    }
    return $sout;
  }
  protected function subSection($name,$values,$all) {
    foreach($all as $section) if($name==$section->name) break;
    if($section->name != $name) return null;
    $out = array();
    foreach($values as $i=>$context){
      $out[$i] = $this->processSection($section,$context,$all);
    }
    return $out;
  }
  protected function getValue($dataName,$context) {
    $parts = explode('.',$dataName);
    foreach($parts as $part){
      if(preg_match('#^\[(\d+)-(\d+)\]$#',$part,$matches)) {
        $output = array();
        for($i = (int)$matches[1]; $i <= (int)$matches[2]; $i++){
          if(isset($context[$i])) $output[$i] = $context[$i];
          else return $output;
        }
      }
      if(is_numeric($part)) $part = (int)$part;
      if(empty($context[$part])) return null;
      $context = $context[$part];
    }
    return $context;
  }
}