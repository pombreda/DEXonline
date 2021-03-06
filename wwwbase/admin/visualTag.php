<?php
require_once('../../phplib/util.php');
util_assertModerator(PRIV_VISUAL);
util_assertNotMirror();
RecentLink::createOrUpdate('Etichetare Imagini Definiții');

$rootPath = util_getImgRoot() . '/';
$savedTags = '';
$action = util_getRequestParameter('action');
$tagging = util_getRequestParameter('tagging');

// Marks the image as completely revised 
if($action == 'finishedTagging') {
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);
  
  if(!empty($line)) {
    $line->revised = 1;
    $line->save();
  }

  FlashMessage::add('Modificările au fost salvate. Mulțumim!');
  util_redirect(util_getWwwRoot() . 'admin/visualTag.php');

// Sets the lexeme associated with the image 
} else if($action == 'setImgLexemeId') {
  $imgLexemeId = util_getRequestParameter('imgLexemeId');
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);
  
  if(!empty($line)){
    $line->lexemeId = $imgLexemeId;
    $line->save();
  }

  $tagging = true;
  $imgToTag = $line->id;

// Resets the lexeme associated with the image 
} else if($action == 'resetImgLexemeId') {
  $imageId = util_getRequestParameter('imageId');

  $line = Visual::get_by_id($imageId);

  if(!empty($line)) {
    $line->lexemeId = '';
    $line->save();
  }

  $tagging = true;
  $imgToTag = $line->id;
}

SmartyWrap::assign('sectionTitle', 'Etichetare imagini pentru definiții');
SmartyWrap::addCss('jcrop', 'select2', 'jqgrid', 'jqueryui', 'gallery');
SmartyWrap::addJs('jquery', 'jcrop', 'visualTag', 'select2', 'select2Dev', 'jqgrid', 'gallery'); 

// Checks which of the visualTag page needs to be loaded
// visualTag.ihtml is for the tables with the revised/unrevised images
// visualTagging.ihtml is for the tagging process
if($tagging) {
  $imgToTag = isset($imgToTag) ? $imgToTag : util_getRequestParameter('imgToTag');
  $line = Visual::get_by_id($imgToTag);
  if($line->revised) {
    $line->revised = 0;
    $line->save();
  }

  $imagePath = $rootPath . $line->path;
  $imageId = $line->id;
  $imgLexemeId = $line->lexemeId;
  if($imgLexemeId) {
    $lexemeName = Lexem::get_by_id($imgLexemeId);
    $lexemeName = $lexemeName->formUtf8General;
    SmartyWrap::assign('lexemeName', $lexemeName);
  }

  SmartyWrap::assign('imagePath', $imagePath);
  SmartyWrap::assign('imageId', $imageId);
  SmartyWrap::assign('imgLexemeId', $imgLexemeId);

  SmartyWrap::displayAdminPage('admin/visualTagging.ihtml');

} else {
  SmartyWrap::displayAdminPage('admin/visualTag.ihtml');
}
