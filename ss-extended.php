<?php
	/**
	 * 30 April - 10 May 2018
	 *
	 *
	 * Make sure that you create an 'uploads' folder where this script resides.
	 * Label color is controlled by class 'bmLabel'.
	 *
	**/
	define('SCRIPT_TITLE', 'SS Bookmarks - Extended');
	define('SCRIPT_VERSION', '0.4.1');
	define('SCRIPT_AUTHOR', 'Dominic Manley, Kufbwxfiwy');
	define('SCRIPT_HOMEPAGE', 'https://github.com/kufbwxfiwy/ss-bookmarks-extended');
	error_reporting(E_ERROR);
	/*******************************************************************************/
	/* Script configuration                                                        */
	/*******************************************************************************/
	$sPageTitle				= SCRIPT_TITLE . ' v ' . SCRIPT_VERSION;		// the page title (typically shown in the browser title bar)
	$sScriptName 			= basename(__FILE__);							// filename of this script (best not to change)
	$bEnableJavascript		= true;											// provides some UI improvements
	$sNoTagLabel			= 'no-tags';									// default label for bookmarks with no tags
	$sLinkTarget			= '_blank';										// target for all links ('_self' will open in same window, '_blank' in a new window)
	$bEnableBackups			= false;										// backup you script (and bookmark data)
	$bBackupFilename		= $sScriptName . '.bck.' . date('ymd');			// filename to backup to (using date('ymd') will increment daily)
	$iViewPortWidth			= 600;											// viewport width in pixels (zooms in and eliminates white-space on iDevices)
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!DO NOT EDIT ANYTHING BELOW THIS LINE!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*DATA-START*/
	$fSize = 14;
	$sortaccordingto = 'label';
	$showurl = true;
	$iNextIndex = 1;
	$aBookmarks = array(
		0 => array('label' => 'Example', 'url' => 'http://example.com', 'tags' => '', 'description' => 'Example Link', 'uref' => '1853099323'),
	);
	$aRemove = array(
		0 => 'http://',
		1 => 'https://',
	);
	/*DATA-END*/

	/*******************************************************************************/
	/* Add/delete bookmarks                                                        */
	/*******************************************************************************/
	
	$bReWriteScript = false; // do we rewrite the script with updated data?
	$sNewBookmark = '';
	$uploadOk = 1;
	$dtagFlag = 0;
	$deleteEverything = false;

if(isset($_GET["RadioButton1"])) {
	if($_GET["RadioButton1"] == 'Label_and_Url') $showurl = true; else $showurl = false;
}
if(isset($_GET["RadioButton2"])) {
	if($_GET["RadioButton2"] == 'Label') $sortaccordingto = 'label'; else $sortaccordingto = 'url';
}
if(isset($_GET["return"]) || isset($_GET["menu_2"])) $bReWriteScript = true; //to add functionality for 'More' button

if(isset($_GET["deletetag"])) {
  $dtag = $_GET["dtag"];
  if ($dtag == $sNoTagLabel) $dtag = '';
  foreach ($aBookmarks as $key => $value)
  {
    if ($value['tags'] == $dtag) {unset($aBookmarks[$key]); $dtagFlag = 1;}
    else if (strstr($value['tags'], $dtag)) {
      $repiecetag = '';
      $dtagpieces = explode(',', $value['tags']);
      foreach ($dtagpieces as $rrvalue) {
        if (!strstr($dtag, trim($rrvalue))) {
          $repiecetag .= trim($rrvalue) . ",";
        }
      }
      $repiecetag = rtrim($repiecetag, ',');
      $aBookmarks[$key]['tags'] = $repiecetag; //directly using $value doesn't work
	$dtagFlag = 1;
    }
  }
  $bReWriteScript = true;
}

if(isset($_GET["delete"])) {
  if(count($aBookmarks) > 0) $deleteEverything = true;
  unset($aBookmarks);
  $bReWriteScript = true;
}

if(isset($_POST["uploadfile"])) {
  $target_dir = "uploads/";
  $target_file = $target_dir.basename($_FILES["fileToUpload"]["name"]);
  $dFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  $tagfield = trim($_POST["tagfield"]);

  $repiecetag = '';
  $dtagpieces = explode(',', $tagfield);
  foreach ($dtagpieces as $tvalue) {
    $repiecetag .= trim($tvalue) . ",";
  }
  $repiecetag = rtrim($repiecetag, ',');
  $tagfield = $repiecetag;

  // Check file size
  if ($_FILES["fileToUpload"]["size"] > 500000) {
      $uploadError = 'Sorry, file is too large.';
      $uploadOk = 0;
  }
  // Allow certain file formats
  if($dFileType != "htm" && $dFileType != "html") {
      $uploadError = 'Bad File Type or File not Present!';
      $uploadOk = 0;
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
      goto exitupload;
  // if everything is ok, try to upload file
  } else {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
          $uploadOk = 2;
          $uploadFileName = basename($_FILES["fileToUpload"]["name"]);
      } else {
          echo "Sorry, there was an error uploading your file."; exit();
      }
  }

  $file = fopen($target_file , "r");
  unset($aBookmarks1);
  $pos1 = $pos2 = $pos3 = false;

  while(! feof($file))
  {
    $prev_pos1 = $pos1;
    $prev_pos2 = $pos2;
    $prev_pos3 = $pos3;
    $prev_anch = $anch;
    $prev_desc = $desc;
    $prev_textdesc = '';

    $pos1 = $pos2 = $pos3 = false;
    $line = fgets($file);

    if($pos = stripos($line, 'A HREF='))
    {
      $pos1 = pos;
      $pos = $pos + 8;
      $anchsta = substr($line, $pos); //anchor start
      $pos = stripos($anchsta, '"'); //anchor ends
      $anch = substr($anchsta, 0, $pos); //the anchor
    }

    if($pos1 == true)
    {
      $pos = stripos($anchsta, '>');
      $pos2 = $pos;
      $pos = $pos + 1;
      $descsta = substr($anchsta, $pos); //description starts
      $pos = stripos($descsta, '<'); //description ends
      $desc = substr($descsta, 0, $pos); //the description
    }

    if($pos = stripos($line, '<DD>'))
    {
      $pos3 = $pos;
      $pos = $pos + 4;
      $textdesc = trim(substr($line, $pos)); //textual description starts
    }

    //if $prev_pos1 true and $pos3 true and $pos1 false fill prev & present 3
    if (($prev_pos1 == true) && ($pos3 == true) && ($pos1 == false)) { 
      $aBookmarks1[] = array('label' => $prev_desc, 'url' => $prev_anch, 'tags' => $tagfield, 'description' => $textdesc);
    }

    //if $prev_pos1 true and $pos1 true and $pos3 false fill prev 2
    if (($prev_pos1 == true) && ($pos1 == true) && ($pos3 == false)) {
      $aBookmarks1[] = array('label' => $prev_desc, 'url' => $prev_anch, 'tags' => $tagfield, 'description' => '');
    }

    //if $prev_pos1 true and $pos1 true and $pos3 true fill both prev 2 and present 3
    if (($prev_pos1 == true) && ($pos1 == true) && ($pos3 == true)) {
      $aBookmarks1[] = array('label' => $prev_desc, 'url' => $prev_anch, 'tags' => $tagfield, 'description' => $prev_textdesc);
      $aBookmarks1[] = array('label' => $desc, 'url' => $anch, 'tags' => $tagfield, 'description' => $textdesc);
      $pos1 = false; $pos2 = false; $pos3 = false; 
    }

    //if $pos1 true and $pos3 true fill present 3
    if (($pos1 == true) && ($pos3 == true)) {
      $aBookmarks1[] = array('label' => $desc, 'url' => $anch, 'tags' => $tagfield, 'description' => $textdesc);
      $pos1 = false; $pos2 = false; $pos3 = false; 
    }

    //if $prev_pos1 true and $pos1 false and $pos3 false fill prev 2
    if (($prev_pos1 == true) && ($pos1 == false) && ($pos3 == false)) {
      $aBookmarks1[] = array('label' => $prev_desc, 'url' => $prev_anch, 'tags' => $tagfield, 'description' => '');
    }
  }

  //if $pos1 true fill present 2
  if ($pos1 == true) {
    $aBookmarks1[] = array('label' => $desc, 'url' => $anch, 'tags' => $tagfield, 'description' => '');
  }

  fclose($file);
  foreach ($aBookmarks1 as $key => $value) if ($value['label'] == '') unset($aBookmarks1[$key]);
  $aBookmarks = array_merge($aBookmarks, $aBookmarks1);
  $iNextIndex = count($aBookmarks);
  $bReWriteScript = true;
  exitupload:
}

if ($_POST['sizetext'] == ' Apply ') $bReWriteScript = true;

if(isset($_GET["removelist"])) {
  $value = trim($_GET["removeurl"]);
  lp1:
  $value = rtrim($value, '\\'); //was breaking the script!
  $value = rtrim($value, '/');
  if ($value[(strlen($value) - 1)] == '\\') goto lp1;
  if ($value{strlen($value)-1} == ':') $value .= '//';
  if ($value != '') {
    $aRemove[] = $value;
    $aRemove = array_unique($aRemove);
    sort($aRemove);
    $bReWriteScript = true;
  }
}

if(isset($_GET["ureset"])) {
  $rrvalue = $_GET["url-reset"];
  if ($rrvalue != '') { //button could be pressed without selection
    foreach ($aRemove as $key => $value) {
      if ($value == $rrvalue) unset($aRemove[$key]);
    }
    $bReWriteScript = true;
  }
}
	
	if (isset($_GET['submit'])) {
		$geturl = trim($_GET['url']);
		$getlabel = trim($_GET['label']);
		$getdesc = trim($_GET['description']);
		$gettags = trim($_GET['tags']);
		$repiecetag = '';
		$dtagpieces = explode(',', $gettags);
		foreach ($dtagpieces as &$tvalue) $tvalue = trim($tvalue);
		$dtagpieces = array_unique($dtagpieces);
		sort($dtagpieces);
		foreach ($dtagpieces as $tvalue) {
		  if ($tvalue == $sNoTagLabel) $tvalue = ''; //'no-tags' is not an allowed value
		  $repiecetag .= trim($tvalue) . ",";
		}
		$repiecetag = rtrim($repiecetag, ',');
		$gettags = $repiecetag;
		if ($getlabel == false) $getlabel = $geturl;
		if ($geturl == true) {
		  $aNewBookmark = array(
			  'label' => $getlabel,
			  'url' => $geturl,
			  'tags' => $gettags,
			  'description' => $getdesc
		  );
		  $aBookmarks[$iNextIndex] = $aNewBookmark; // add new bookmark at next index
		  $iNextIndex++; // updated the index for next time
		  $sNewBookmark = $geturl;
		  $bReWriteScript = true;
		}
	}
	
	if (isset($_GET['uid']) && $_GET['action'] == 'delete') {
		$aBookmarkTags = explode(',', $aBookmarks[intval($_GET['uid'])]['tags']);
		// first just remove the tag from the bookmark's record (it may have more than one)
		foreach ($aBookmarkTags as $iIndex => $aBookmarkTag) {
			if ($aBookmarkTag == $_GET['tag']) {
				unset($aBookmarkTags[$iIndex]);
			}
		}
		$aBookmarks[intval($_GET['uid'])]['tags'] = implode(',', $aBookmarkTags);
		// if there are no more tags for this bookmark, remove the record completely
		if ($aBookmarks[intval($_GET['uid'])]['tags'] == '') {
			unset($aBookmarks[intval($_GET['uid'])]);
		}
		$bReWriteScript = true;
	}
	
	if ($bReWriteScript) {
		reset($aBookmarks);
		function cmp($a, $b){
		  return strcmp($a["tags"].$a["url"], $b["tags"].$b["url"]);
		}
		usort($aBookmarks, "cmp"); //writing this sort wasn't trivial!
		$checkdupvalue = '0'; $checkduptag = '0';
		//eliminate duplicate entries
		foreach ($aBookmarks as $key => $value) {
		  foreach ($aRemove as $rrvalue) { //might as well eliminate unwanted urls at this point
		    if (rtrim($rrvalue, '/') == rtrim($value['url'], '/')) {unset($aBookmarks[$key]); unset($aNewBookmark);}
		  }
		  //let's give a (hopefully) unique reference to the entries...
		  if ($aBookmarks[$key]['uref'] == '') $aBookmarks[$key]['uref'] = mt_rand();
		  if (($value['url'] == $checkdupvalue) && ($value['tags'] == $checkduptag)) unset($aBookmarks[$key]);
		  else {$checkdupvalue = $value['url']; $checkduptag = $value['tags'];}
		}
		$sScriptContents = file_get_contents($sScriptName); // get the contents of this very file
		$sPreData = substr($sScriptContents, 0, strpos($sScriptContents, '/*DATA-START*/') + strlen('/*DATA-START*/')); // grab everything AFTER /*DATA-START*/
		$sAftData = substr($sScriptContents, strpos($sScriptContents, '/*DATA-END*/')); // grab everything UP TO /*DATA-END*/
		$sNewData  = "\n"; // build new data (as PHP) to insert in the middle
		$sNewData .= "\t" . '$fSize = ' . ($_POST['fSize'] ? $_POST['fSize'] : $fSize) . ';' . "\n";
		$sNewData .= "\t" . '$sortaccordingto = \'' . $sortaccordingto . '\';' . "\n";
		$sNewData .= "\t" . '$showurl = ' . ($showurl ? 'true' : 'false') . ';' . "\n";
		$sNewData .= "\t" . '$iNextIndex = ' . $iNextIndex . ';' . "\n";
		$sNewData .= "\t" . '$aBookmarks = array(' . "\n";
		foreach ($aBookmarks as $iIndex => $aBookmark) {
			$sNewData .= "\t\t" . $iIndex . ' => array(\'label\' => \'' . 
				str_replace('\'', '\\\'', $aBookmark['label']) . '\', \'url\' => \'' .
				str_replace('\'', '\\\'', $aBookmark['url']) . '\', \'tags\' => \'' .
				str_replace('\'', '\\\'', $aBookmark['tags']) . '\', \'description\' => \'' .
				str_replace('\'', '\\\'', $aBookmark['description']) . '\', \'uref\' => \'' .
				$aBookmark['uref'] . '\'),' . "\n";

		}
		$sNewData .= "\t" . ');' . "\n";
		$sNewData .= "\t" . '$aRemove = array(' . "\n";
		foreach ($aRemove as $key => $value) {
			$sNewData .= "\t\t" . $key . ' => \'' . str_replace('\'', '\\\'', $value) . '\',' . "\n";
		}
		$sNewData .= "\t" . ');' . "\n\t";
		if ($bEnableBackups) {
			file_put_contents($bBackupFilename, $sScriptContents); // if condfigured to do so, save a copy of current script before overwriting
		}
		file_put_contents($sScriptName, $sPreData . $sNewData . $sAftData); // overwrite current script file with new data
	}
	
	/*******************************************************************************/
	/* Build a tags array from tags used in bookmarks data, sort alphabetically    */
	/*******************************************************************************/
	
	$aTags = array();
	foreach ($aBookmarks as $aBookmark) {
		if ($aBookmark['tags'] != '') {
			$aBookmarkTags = explode(',', $aBookmark['tags']);
			$aTags = array_merge($aTags, $aBookmarkTags);
		}
	}
	$aTags = array_unique($aTags);
	sort($aTags);
	array_unshift($aTags, $sNoTagLabel);
	
	/*******************************************************************************/
	/* Identify current tag, default to no-tags                                    */
	/*******************************************************************************/
	
	$sCurrentTag = $sNoTagLabel;
	if ($_GET['tag'] != '' && in_array($_GET['tag'], $aTags)) {
		$sCurrentTag = $_GET['tag'];
	}
	else if ($_POST['tag'] != '' && in_array($_POST['tag'], $aTags)) {
		$sCurrentTag = $_POST['tag'];
	}

	if (isset($_GET['menu_1']) || isset($_POST['menu_1']) || isset($_GET['deletetag']) || isset($_GET['delete'])) $inMenu1 = true; else $inMenu1 = false;
	if (isset($_GET['menu_2']) || isset($_POST['fSize']) || isset($_GET['removelist']) || isset($_GET['ureset'])) $inMenu2 = true; else $inMenu2 = false;
	if ($inMenu1 == false && $inMenu2 == false) $inMain = true; else $inMain = false;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=<?php echo $iViewPortWidth; ?>" />
	<title><?php echo htmlentities($sPageTitle); ?></title>
	<style type="text/css">
		/** 				{font-family: Arial, Helvetica, sans-serif; font-size: 11px; }*/
		body 				{font-family: Times New Roman, Arial, Helvetica, sans-serif; font-size: 16px; background: #ffffff}
		a 				{color: blue;}
		a:hover 		{color: #F60;}
		form			{margin: 0 auto; padding: 0px;}
		#tagForm		{padding-bottom: 10px; border-bottom: 1px solid #CCC;}
		ul 				{margin: 0; padding: 0; margin-top: 10px; margin-bottom: 10px; border-bottom: 1px solid #CCC; padding-bottom: 5px;}
		li 				{list-style: none; margin-bottom: 5px; font-size: <?php echo ($fSize * 1.2) ?>px;}
		.bmLabel		{color: red;} /* label text-color */
		.bmLink 		{float: left;}
		.bmEdit 		{float: right;}
		.bmEdit a		{display: block; padding: 0 5px; background: #EEE; text-decoration: none;}
		.bmEdit a:hover	{background: #CCC;}
		.empty			{margin-bottom: 10px; border-bottom: 1px solid #CCC; padding-bottom: 10px;}
		#addForm		{margin: 0 auto; padding: 10px; width: 300px; background: #EEE;}
		#addForm label	{display: block; float: left; width: 100px; margin: 5px; text-align: right; clear: left;}
		#addForm input	{margin-top: 4px;}
		#addButton		{margin-left: 110px; clear: left; _margin-left: 118px;}
		#label 		{color: blue;}
		#url 			{width: 160px;}
		#tags 		{width: 100px;}
		#submitButton 	{float: right; margin-right: 15px;}
		#tag 		{width: 100px;}
		table td {
			/* border: 1px solid black; /* Style just to show the table cell boundaries */
		}
		td{font-size : 12pt;}
		.clear 			{clear: both;}
		.theader {text-align: center; font-weight: bold;}
		.tinput {text-align: center;}
		.aright {text-align: right;}
		.aleft {text-align: left;}
		.acenter {text-align: center;}
		.inst {color: black; background-color: white;}
		label {padding-left: 0px;}
		textarea {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px; 
			border: 2px solid #ccc;
			background-color: #f8f8f8;
			resize: none;
			overflow-x: hidden;
		}
		.btminf {color: blue;}
		.btmtinf {color: blue; /*font-weight: bold;*/}
		.btmerr {color: red; /*font-weight: bold;*/}
		.returnmain {
			width: 20px;
			height: 30px;
			padding-right: 0px;
			padding-left: 0px;
			padding-bottom: 0px;
			padding-top: 0px;
			margin: 0px;
			margin-top: 0px;
			margin-bottom: 0px;
			background-image:  url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAeCAIAAACjcKk8AAAABGdBTUEAALGPC/xhBQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAKmSURBVEhLjZW7S7JRHMd9nQwSJxeHVlNoCHEQFCQRXLyA0GBLi4M6CE5p6JguJjToGLS4FP4BLu4GGeQSappdJG95v+f79T1wfHx89O1MD+d8P+d3P8+ffD7P41oCgaBSqfj9/slk4vP5ZDJZr9djCwGvr5d/iylNp9OlUoml5HHCn5+fFxcXTNjpdH59ff0KLpfLiUSCCV9dXX18fPwKhqjT6dhsNsJrNJput7vuI7fbCA9q+JnL5b6/v3ERZ3RsGHlqt9uPj4/7+/vErFKpBF8oFP5juVarVatVi8XCKolOp8ON22Acn5+fc9Ycm9lsdmOpGo2G3W7fRGJfq9WuR76IGXGiDIQUCoXBYJDzlnXjCxj5sFqtBHh4eAgEApzw0dHReDxGC9Dk8YrFIupB1AcHB/P5XKFQMGE+n39yckJ2QqFQMpmEMfT5wllEi3jI2f39/c/PD8vs4eEhCs7axNjc3NzwkGSRSIQztVo9HA4zmQxLh66ezWZut5u1L5FIFm4/PT2dnZ3BDcxgLBZjia6vr1F/GB+NRs/PzzDocrngzt3d3SJh4IEhDegQh8Oxni3c/v7+TpT4gKzZbL69va20JzKhUqk4Uw0S6m1T1e/3d3Z2CGw0GuEb8yLYfH19ZfJLy3Ablqna4/GgbGKxmMmT6Ci/hHFrKpWiUuQJUrTk7u4u3QyHw8wnYQnj6YlGo1SHqcQkYCE36FmyH4lEIOOwPJ1OpVIphQeDAREhluPj420waogMUTIej1P36vW62WzeCLdaLeYweL1e5uhvg9E6t7e31KbBYMDoMOuxDUZiKLm3t4f/A6sTtsFyuZzC8B8PwzpsMpmI5vLyciXb9AAMq4HILZgK+lSglit1RhkxKOgbPBGcjzP5b+n1+tPTUww/U/MX1+DfrIbEVCgAAAAASUVORK5CYII=');
		}
	</style>
	<script type="text/javascript">
		var fChangeTag = function (o) {
			// produces cleaner urls...
			var sUrl = '<?php echo str_replace('\'', '\\\'', urlencode($sScriptName)); ?>';
			if (o.value != '<?php echo str_replace('\'', '\\\'', $sNoTagLabel); ?>') {
				sUrl += '?tag=' + encodeURIComponent(o.value)
			}
			window.location.href = sUrl;
		}
<?php
		if ($inMenu1 || $inMenu2) {
			echo "\t\t" . 'if (window.innerHeight) bdht = (window.innerHeight - 530)/2;' . "\n";
			echo "\t\t" . 'else bdht = 30;' . "\n";
		}
?>
		window.onload = function(){
			var sUrl = window.location.href;
			if (sUrl.includes('&uid=')) window.location.href = sUrl.replace('&uid=', '&id='); //avoids problems with refreshes
<?php
		if ($inMenu1 || $inMenu2) echo "\t\t\t" . 'document.getElementById("aspacer").style.height = bdht + \'px\';' . "\n";
		if ($inMenu2) echo "\t\t\t" . 'document.getElementById("loading").style.display = "none";' . "\n";
?>
		}
<?php
		if ($inMenu2) {
		echo <<<END
		function finst(){
			setTimeout(function(){document.getElementById('loading').style.display = "block"}, 100); //text delay
			document.getElementById('textchange').style.display = "none";
		}

END;
		}
?>
	</script>
</head>
<body>
<?php
if ($inMain) {
echo '<ul style="text-align: center; line-height: 50%;"> <h3>' . $sPageTitle . '</h3><h4>Originally written by DM (<A HREF="https://github.com/dominicwa/ss-bookmarks">https://github.com/dominicwa/ss-bookmarks</A>)</h4>' . "\n";
echo '<h4>Extended by KU (<A HREF="https://github.com/kufbwxfiwy/ss-bookmarks-extended">https://github.com/kufbwxfiwy/ss-bookmarks-extended</A>)</h4>' . "\n";
echo '</ul>' . "\n";
echo '	<form action="'; echo htmlentities($sScriptName); echo '" method="get" id="tagForm">' . "\n";
echo '		<label style="margin-left: 10px">View&nbsp;Tag:&nbsp;</label><select name="tag" id="tag" onchange="fChangeTag(this);">';
			
				echo "\n";
				for ($i = 0; $i < sizeof($aTags); $i++) {
					$sSelected = '';
					if ($sCurrentTag == $aTags[$i]) {
						$sSelected = ' selected="selected"'; // select the tag currently displaying
					}
					echo "\t\t\t" . '<option value="' . htmlentities($aTags[$i]) . '"' . $sSelected . '>' . htmlentities($aTags[$i]) . '</option>' . "\n";
				}
				echo "\t\t";
			
echo '</select>' . "\n";
echo '		<input type="submit" name="menu_1" id="submitButton" value="Go to Menu" />' . "\n";
echo '	</form>';
	
		echo "\n";
		$aCurrentTagBookmarks = array();
		foreach ($aBookmarks as $iIndex => $aBookmark) {
			$aBookmarkTags = explode(',', $aBookmark['tags']);
			if (in_array($sCurrentTag, $aBookmarkTags) || ($aBookmark['tags'] == '' && $sCurrentTag == $sNoTagLabel)) {
				// here we use the label and uid (to maintain uniqueness) as the key instead so it's easier to sort later
				if ($sortaccordingto == 'label') {
				  $aCurrentTagBookmarks[($aBookmark['label'] . $iIndex)] = array( //sorting according to label
					  'uid' => $iIndex,
					  'label' => $aBookmark['label'],
					  'url' => $aBookmark['url'],
					  'description' => $aBookmark['description'],
					  'uref' => $aBookmark['uref']
				  );
				}
				if ($sortaccordingto == 'url') {
				  $aCurrentTagBookmarks[($aBookmark['url'] . $iIndex)] = array( //sorting according to url
					  'uid' => $iIndex,
					  'label' => $aBookmark['label'],
					  'url' => $aBookmark['url'],
					  'description' => $aBookmark['description'],
					  'uref' => $aBookmark['uref']
				  );
				}
			}
		}
		//ksort($aCurrentTagBookmarks);
		uksort($aCurrentTagBookmarks, 'strnatcasecmp');
		if (sizeof($aCurrentTagBookmarks) > 0) {
			echo "\t" . '<ul>' . "\n";
		}
		if (!$showurl) {
		  foreach ($aCurrentTagBookmarks as $aCurrentTagBookmark) {
			  $nextElm = current($aCurrentTagBookmarks); //the 'current' element is already one element ahead of the already fetched!
			  if ($nextElm) {
				$nextRef = $nextElm['uref'];
				next($aCurrentTagBookmarks); //then you MUST continue using next, otherwise you stick!
			  } else { //caters for the last element
				end($aCurrentTagBookmarks);
				prev($aCurrentTagBookmarks);
				$nextElm = current($aCurrentTagBookmarks);
				$nextRef = $nextElm['uref'];
			  }
			  echo "\t\t" . '<li id="' . $aCurrentTagBookmark['uref'] . '">' . "\n";
			  echo "\t\t\t" . '<div class="bmLink">' . "\n";
			  echo "\t\t\t\t" . '<a href="' . $aCurrentTagBookmark['url'] . '" target="' . $sLinkTarget . '">' . $aCurrentTagBookmark['label'] . '</a>' . "\n";
			  echo "\t\t\t" . '</div>' . "\n";
			  echo "\t\t\t" . '<div class="bmEdit">' . "\n";
			  echo "\t\t\t\t" . '<a href="?action=delete&uid=' . $aCurrentTagBookmark['uid'] . '&tag=' . urlencode($sCurrentTag) . '#' . $nextRef . '">-</a>' . "\n";
			  echo "\t\t\t" . '</div>' . "\n";
			  if ($aCurrentTagBookmark['description']) echo "\t\t\t\t" . '<br><div style="text-indent: 25px">' . $aCurrentTagBookmark['description'] . '</div>' . "\n";
			  echo "\t\t\t" . '<div class="clear"></div>' . "\n";
			  echo "\t\t" . '</li>' . "\n";
		  }
		}
		else {
		  $lastElement = end($aCurrentTagBookmarks);
		  foreach ($aCurrentTagBookmarks as $aCurrentTagBookmark) {
			  $nextElm = current($aCurrentTagBookmarks); //the 'current' element is already one element ahead of the already fetched!
			  if ($nextElm) {
				$nextRef = $nextElm['uref'];
				next($aCurrentTagBookmarks); //then you MUST continue using next, otherwise you stick!
			  } else { //caters for the last element
				end($aCurrentTagBookmarks);
				prev($aCurrentTagBookmarks);
				$nextElm = current($aCurrentTagBookmarks);
				$nextRef = $nextElm['uref'];
			  }
			  echo "\t\t" . '<li id="' . $aCurrentTagBookmark['uref'] . '">' . "\n";
			  echo "\t\t\t" . '<div class="bmLabel">' . $aCurrentTagBookmark['label'] . '</div>' . "\n";
			  echo "\t\t\t" . '<div class="bmLink">' . "\n";
			  echo "\t\t\t\t" . '<a href="' . $aCurrentTagBookmark['url'] . '" target="' . $sLinkTarget . '">' . $aCurrentTagBookmark['url'] . '</a>' . "\n";
			  echo "\t\t\t" . '</div>' . "\n";
			  echo "\t\t\t" . '<div class="bmEdit">' . "\n";
			  echo "\t\t\t\t" . '<a href="?action=delete&uid=' . $aCurrentTagBookmark['uid'] . '&tag=' . urlencode($sCurrentTag) . '#' . $nextRef . '">-</a>' . "\n";
			  echo "\t\t\t" . '</div>' . "\n";
			  if ($aCurrentTagBookmark['description']) echo "\t\t\t" . '<br><div style="text-indent: 25px">' . $aCurrentTagBookmark['description'] . '</div>' . "\n";
			  else echo "\t\t\t" . '<div>&nbsp;</div>' . "\n";
			  if ($aCurrentTagBookmark != $lastElement) echo "\t\t\t" . '<br>' . "\n";
			  echo "\t\t\t" . '<div class="clear"></div>' . "\n";
			  echo "\t\t" . '</li>' . "\n";
		  }
		}
		if (sizeof($aCurrentTagBookmarks) > 0) {
			echo "\t" . '</ul>' . "\n";
		} else {
			echo "\t" . '<p class="empty">No bookmarks in "' . htmlentities($sCurrentTag) . '".</p>' . "\n";
		}
		echo "\t";
}
?>
<?php
if ($inMenu1) {
echo <<<END
<div id="aspacer">
</div>
<table align="center" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td><h2 style="margin-bottom: 0px; padding-bottom: 0px" class="tinput">User: Guest_001</h2>
			<table cellspacing="2" cellpadding="2" border="0" style="WIDTH: 644px">
				<tr valign="top">
					<td>
						<table style="WIDTH: 324px" cellspacing="1" cellpadding="2" border="0"><!-- master width for the column -->
							<tr valign="top">
								<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="post" enctype="multipart/form-data">
									<table style="WIDTH: 323px; HEIGHT: 168px" cellspacing="1" cellpadding="2" align="center" border="0" bgcolor="#dddddd">
										<tr>
											<td style="TEXT-ALIGN: center"><font style="FONT-WEIGHT: bold">Upload Bookmarks File</font><br>(from Firefox, SeaMonkey, etc...)
											</td>
										</tr>
										<tr>
											<td class="tinput">
												<table style="WIDTH: 319px" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td class="aright">
															<label>Select&nbsp;File:</label>
														</td>
														<td style="text-align: left">
															<input style="WIDTH: 180px" id="fileToUpload" type="file" name="fileToUpload">
														</td>
													</tr>
													<tr>
														<td class="aright">
															<label style="PADDING-LEFT: 8px">Give&nbsp;Tag(s):</label>
														</td>
														<td style="text-align: left">
															<input style="WIDTH: 100px" name="tagfield">
														</td>
													</tr>
												</table>&nbsp;<br>
												<input type="submit" value="Upload File" name="uploadfile">
												<input type="hidden" name="menu_1" value="menu_1" />
											</td>
										</tr>
									</table></form>
								</td>
							</tr>
							<tr style="HEIGHT: 7px" valign="top">
								<td>
								</td>
							</tr>
							<tr valign="top"><!--bottom row left-->
								<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="get">
									<table style="WIDTH: 323px; HEIGHT: 175px" cellspacing="1" cellpadding="2" align="center" bgcolor="#dddddd" border="0">
										<tr>
											<td class="theader">Add Single Bookmarks
											</td>
										</tr>
										<tr>
											<td class="tinput">
												<table align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td class="aright">
															<label>Label:</label>
														</td>
														<td>
															<input type="text" name="label" value="">
														</td>
													</tr>
													<tr>
														<td class="aright">
															<label>URL:</label>
														</td>
														<td>
															<input type="text" name="url" value="http://">
														</td>
													</tr>
													<tr>
														<td class="aright">
															<label>Description:</label>
														</td>
														<td>
															<input type="text" name="description" id="description" value="">
														</td>
													</tr>
													<tr>
														<td class="aright">
															<label>Tags&nbsp;(csv):</label>
														</td>
														<td>
															<input type="text" name="tags" value="
END;
															if (htmlentities($gettags)) echo $gettags; else if (htmlentities($sCurrentTag) == $sNoTagLabel) echo ''; else echo $sCurrentTag; echo <<<END
">
														</td>
													</tr>
												</table>
												<input type="hidden" value="
END;
												echo htmlentities($sCurrentTag); echo <<<END
" name="tag">
												<input type="submit" value="Add Bookmark" name="submit">
												<input type="hidden" name="menu_1" value="menu_1" />
											</td>
										</tr>
									</table></form>
								</td>
							</tr>
						</table>
					</td>
					<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="get">
						<table style="WIDTH: 320px" cellspacing="1" cellpadding="2" border="0"><!-- master width for the column -->
							<tr style="HEIGHT: 168px" valign="top">
								<td>
									<table style="WIDTH: 319px; HEIGHT: 168px" align="center" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td class="theader">Delete Multiple Entries
											</td>
										</tr>
										<tr><!--don't valign to top-->
											<td>
												<table style="WIDTH: 312px" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td class="aright">
															<label style="PADDING-LEFT: 8px">For&nbsp;</label>
														</td>
														<td>
END;
echo "\n";
echo '															<select name="dtag" id="tag">';
echo "\n";
for ($i = 0; $i < sizeof($aTags); $i++) {
	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option value="' . htmlentities($aTags[$i]) . '">' . htmlentities($aTags[$i]) . '</option>' . "\n";
}
echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
echo '</select>' . "\n";
echo <<<END
														</td>
														<td>
															<input type="submit" value="Delete Entries" name="deletetag">
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td>
											</td>
										</tr>
										<tr>
											<td class="tinput">
												<input type="submit" value="Delete Everything" name="delete"><br><font style="FONT-WEIGHT: bold; COLOR: red">⚠Use this button with caution!</font>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr style="HEIGHT: 7px" valign="top">
								<td>
								</td>
							</tr>
							<tr valign="top"><!--bottom row right-->
								<td>
									<table style="WIDTH: 319px; HEIGHT: 83px" align="center" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td class="theader">Display Options
											</td>
										</tr>
										<tr valign="top">
											<td class="tinput">
												<table align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td class="tinput">
															<label>Show:&nbsp;&nbsp;</label>
															<label>Label&nbsp;Only</label>

END;
echo '															<input type="radio" '; if(!$showurl) echo 'checked '; echo 'value="Label_Only" name="RadioButton1">' . "\n";
echo '															<label>&nbsp;&nbsp;Label&nbsp;&amp;&nbsp;URL</label>' . "\n";
echo '															<input type="radio" '; if($showurl) echo 'checked '; echo 'value="Label_and_Url" name="RadioButton1">' . "\n";
echo '														</td>' . "\n";
echo '													</tr>' . "\n";
echo '													<tr>' . "\n";
echo '														<td class="tinput">' . "\n";
echo '															<label>Sort&nbsp;according&nbsp;to:&nbsp;&nbsp;</label>' . "\n";
echo '															<label>Label</label>' . "\n";
echo '															<input type="radio" '; if($sortaccordingto == 'label') echo 'checked '; echo 'value="Label" name="RadioButton2">' . "\n";
echo '															<label>&nbsp;&nbsp;URL</label>' . "\n";
echo '															<input type="radio" '; if($sortaccordingto == 'url') echo 'checked '; echo 'value="Url" name="RadioButton2">' . "\n";
echo '															<input type="hidden" value="'; if (htmlentities($sCurrentTag) == $sNoTagLabel) echo ''; else echo $sCurrentTag; echo '" name="tag">' . "\n"; echo <<<END
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr style="HEIGHT: 4px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table style="WIDTH: 319px; HEIGHT: 34px" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td>
												<table align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td><strong>More&nbsp;Options</strong>
														</td>
														<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
														</td>
														<td>
															<input type="submit" value=" More " name="menu_2">
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr style="HEIGHT: 4px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table style="WIDTH: 319px; HEIGHT: 34px" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td>
												<table align="center" cellspacing="0" cellpadding="0" border="0">
													<tr>
														<td style="WIDTH: 24px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 125px" class="tinput"><strong>Return&nbsp;to&nbsp;Main&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 71px">
															<input type="submit" value=" Return " name="return">
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 20px"><!-- spacer here -->
															<div class="returnmain">&nbsp;
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table></form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

END;
	if ($aNewBookmark) {
		echo '<div class="tinput">The following URL: <font class="btminf">' . $aNewBookmark['url'] . '</font> was added to Bookmarks within: <font class="btminf">';
		if ($aNewBookmark['tags']) echo $aNewBookmark['tags']; else echo $sNoTagLabel; echo '</font></div>';
	}
	else if($sNewBookmark) {
		echo '<div class="tinput">The following URL: <font class="btminf">' . $sNewBookmark . '</font> was not added to Bookmarks because it is present in the remove list.</div>';
	}
	if ($uploadOk == 0) {
		echo '<div class="tinput"><font class="btmerr">Upload Error:</font> ' . $uploadError . '</div>';
	}
	if ($uploadOk == 2) {
		echo '<div class="tinput">The file <font class="btminf">\'' . $uploadFileName . '\'</font> has been uploaded within: <font class="btminf">';
		if ($tagfield) echo $tagfield; else echo $sNoTagLabel; echo '</font></div>';
	}
	if ($dtagFlag == 1) {
		if ($dtag == '') $dtag = $sNoTagLabel;
		echo '<div class="tinput">Tag <font class="btminf"> \'' . $dtag . '\'</font> has been emptied.</div>';
	}
	if ($deleteEverything) {
		echo '<div class="tinput"><b><font class="btmerr">Everything was Deleted!</font></b></div>';
	}
} //END OF 'if ($inMenu1)'

if ($inMenu2) {
$target_dir = "./";
$target_name = 'Bookmarks_' . date("mdy") . '.html'; //to be used where there is no zip support
$target_zip = 'Bkmks_' . date("mdy") . '.zip';
$target_file = $target_dir . $target_zip; 
$downloadOk = 1;

$sNewData = '<!DOCTYPE NETSCAPE-Bookmark-file-1>' . "\n";
$sNewData .='<!-- This is an automatically generated file.' . "\n";
$sNewData .='It will be read and overwritten.' . "\n";
$sNewData .='Do Not Edit! -->' . "\n";
$sNewData .='<TITLE>Bookmarks</TITLE>' . "\n";
$sNewData .='<H1>Bookmarks</H1>' . "\n";
$sNewData .='<DL><p>' . "\n";
$lenBeforeNewData = strlen($sNewData);

if (isset($_GET['downloadbookmarksall'])) {
	if ($sortaccordingto == 'label') {
		function cmp_($a, $b){
			return strcmp($a["label"], $b["label"]);
		}
		usort($aBookmarks, "cmp_");
	} 
	if ($sortaccordingto == 'url') {
		function cmp_($a, $b){
			return strcmp($a["url"], $b["url"]);
		}
		usort($aBookmarks, "cmp_");
	}
	foreach ($aBookmarks as $value) {
		$sNewData .= '<DT><A HREF="' . $value['url'] . '">' . $value['label'] . '</A>' . ($value['description'] ? '<DD>' . $value['description']:'') . "\n";
	}
	$lenAfterNewData = strlen($sNewData);
	if($lenAfterNewData > $lenBeforeNewData) $outputzip = true;
}

if (isset($_GET['downloadbookmarks'])) {
	$sDownloadTag = $_GET["dtag"];
	unset($aCurrentTagBookmarks);
	$aCurrentTagBookmarks = array();
	foreach ($aBookmarks as $iIndex => $aBookmark) {
		$aBookmarkTags = explode(',', $aBookmark['tags']);
		if (in_array($sDownloadTag, $aBookmarkTags) || ($aBookmark['tags'] == '' && $sDownloadTag == $sNoTagLabel)) {
			// here we use the label and uid (to maintain uniqueness) as the key instead so it's easier to sort later
			if ($sortaccordingto == 'label') {
			  $aCurrentTagBookmarks[($aBookmark['label'] . $iIndex)] = array( //sorting according to label
				  'uid' => $iIndex,
				  'label' => $aBookmark['label'],
				  'url' => $aBookmark['url'],
				  'description' => $aBookmark['description']
			  );
			}
			if ($sortaccordingto == 'url') {
			  $aCurrentTagBookmarks[($aBookmark['url'] . $iIndex)] = array( //sorting according to url
				  'uid' => $iIndex,
				  'label' => $aBookmark['label'],
				  'url' => $aBookmark['url'],
				  'description' => $aBookmark['description']
			  );
			}
		}
	}
	//ksort($aCurrentTagBookmarks);
	uksort($aCurrentTagBookmarks, 'strnatcasecmp');
	foreach ($aCurrentTagBookmarks as $value) {
		$sNewData .= '<DT><A HREF="' . $value['url'] . '">' . $value['label'] . '</A>' . ($value['description'] ? '<DD>' . $value['description']:'') . "\n";
	}
	$lenAfterNewData = strlen($sNewData);
	if($lenAfterNewData > $lenBeforeNewData) $outputzip = true;
}

$sNewData .='</DL><p>' . "\n";

if ($outputzip) {
	//file_put_contents($target_dir . $target_name, $sNewData);  //to be used where there is no zip support
	$zip = new ZipArchive();
	$zipFileName = $target_zip;
	$opened = $zip->open( $zipFileName, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
	if( $opened !== true ){
		die("cannot open {$zipFileName} for writing.");
	}
	$zip->addFromString( "bookmarks.html", $sNewData);
	$zip->close();
}

if (isset($_POST['fSize'])) {$fSizeOld = $fSize; $fSize = $_POST['fSize'];}
if ($_POST['sizetext'] == 'Increase') {$fSize++; if ($fSize == 12) $fSize++;}
if ($_POST['sizetext'] == 'Decrease') {$fSize--; if ($fSize == 12) $fSize--;}

echo <<<END
<div id="aspacer">
</div>
<table align="center" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td><h2 style="margin-bottom: 0px; padding-bottom: 0px" class="acenter">User: Guest_001</h2>
			<table cellspacing="2" cellpadding="2" border="0" style="WIDTH: 600px">
				<tr valign="top">
					<td>
						<table style="WIDTH: 324px" cellspacing="1" cellpadding="2" border="0"><!-- master width for the column -->
							<tr valign="top">
								<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="get">
									<table style="WIDTH: 323px; HEIGHT: 223px" align="center" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td class="theader">Download Bookmarks File
											</td>
										</tr>
										<tr valign="bottom"><!-- valign to bottom -->
											<td>
												<table style="WIDTH: 312px" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td class="aright">
															<label style="PADDING-LEFT: 8px">For&nbsp;</label>
														</td>
														<td>
END;
echo "\n";
echo '															<select name="dtag" id="tag">';
echo "\n";
for ($i = 0; $i < sizeof($aTags); $i++) {
	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option value="' . htmlentities($aTags[$i]) . '">' . htmlentities($aTags[$i]) . '</option>' . "\n";
}
echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
echo '</select>' . "\n";
echo <<<END
														</td>
														<td style="PADDING-RIGHT: 2px">
															<input style="WIDTH: 95%" type="submit" value="Download Bookmarks" name="downloadbookmarks">
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td style="HEIGHT: 35px" class="acenter">OR
											</td>
										</tr>
										<tr valign="top">
											<td>
												<table style="WIDTH: 312px" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td style="WIDTH: 31px">&nbsp;
														</td>
														<td class="aright">
															<input style="WIDTH: 97%" type="submit" value="Download Bookmarks" name="downloadbookmarksall">
															<input type="hidden" value="true" name="menu_2">
															<input type="hidden" value="
END;
												echo htmlentities($sCurrentTag); echo <<<END
" name="tag">
														</td>
														<td class="aleft" style="WIDTH: 115px">
															<label style="PADDING-LEFT: 8px">For All Tags</label>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>

END;
if ($outputzip){
	echo "\t\t\t\t\t\t\t\t\t\t\t" . '<td style="text-align: left;  padding-left: 20px; border-top: 1px solid black" height="40">' . "\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t" . 'If download does not start automatically click<br>this link: <a href="' . $target_file . '">' . $target_zip . '</a>' . "\n";
}
else if($_GET['downloadbookmarks'] || $_GET['downloadbookmarksall']){
	echo "\t\t\t\t\t\t\t\t\t\t\t" . '<td style="text-align: center; border-top: 1px solid black" height="40">' . "\n";
	echo "\t\t\t\t\t\t\t\t\t\t\t" . '<font class="btmtinf">Tag is Empty!</font>' . "\n";
}
else echo "\t\t\t\t\t\t\t\t\t\t\t" . '<td style="text-align: left;  padding-left: 20px; border-top: 1px solid black" height="40">' . "\n";
echo "\t\t\t\t\t\t\t\t\t\t\t" . '</td>' . "\n";
echo "\t\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
echo "\t\t\t\t\t\t\t\t\t" . '</table>';
echo '</form>' . "\n";
echo <<<END
								</td>
							</tr>
							<tr style="HEIGHT: 7px" valign="top">
								<td>
								</td>
							</tr>
							<tr>
								<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="post" enctype="multipart/form-data">
									<table style="WIDTH: 323px; HEIGHT: 200px" cellspacing="1" cellpadding="2" align="center" border="0" bgcolor="#dddddd">
										<tr>
											<td style="PADDING-TOP: 8px; TEXT-ALIGN: center" height="30"><font style="FONT-WEIGHT: bold">Adjust Text Size</font>
											</td>
										</tr>
										<tr>
											<td class="tinput">
												<table style="WIDTH: 280px; HEIGHT: 85px" bgcolor="#ffffff" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td style="text-align: center;"><li style="font-size: 
END;
echo ($fSize * 1.2); echo <<<END
px;">
														Text Text Text Text<br>
														<font style="color: blue; text-decoration: underline">Link Link Link Link</font><br>
														Text Text Text Text</li>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td class="tinput" height="20">
												<span id="loading" style="FONT-SIZE: 14px"><font class="inst">Refreshing...</font></span>

END;
if (($_POST['sizetext'] == ' Apply ') && ($fSizeOld != $fSize)) echo '<span id="textchange" style="font-size: 14px"><font class="btmtinf">Text Size Changed!</font></span>' . "\n";
echo <<<END
											</td>
										</tr>
										<tr valign="top">
											<td class="tinput" height="40">

END;
echo '												<input type="submit" value="Increase" name="sizetext" onClick="javascript: finst()"'; if ($fSize > 15) echo ' disabled'; echo '>&nbsp;&nbsp;&nbsp;&nbsp;' . "\n";
echo '												<input type="submit" value="Decrease" name="sizetext" onClick="javascript: finst()"'; if ($fSize < 11) echo ' disabled'; echo '>&nbsp;&nbsp;&nbsp;&nbsp;' . "\n";
echo '												<input type="submit" value=" Apply " name="sizetext">&nbsp;&nbsp;&nbsp;' . "\n";
echo '												<input type="hidden" value="'; if (htmlentities($sCurrentTag) == $sNoTagLabel) echo ''; else echo $sCurrentTag; echo '" name="tag">' . "\n";
echo '												<input type="hidden" value="';
echo $fSize; echo <<<END
" name="fSize">
											</td>
										</tr>
									</table></form>
								</td>
							</tr>
						</table>
					</td>
					<td><form action="
END;
echo htmlentities($sScriptName); echo <<<END
" method="get">
						<table align="center" style="WIDTH: 320px" cellspacing="1" cellpadding="2" border="0"><!-- master width for the column -->
							<tr style="HEIGHT: 168px" valign="top">
								<td>
									<table style="WIDTH: 319px; HEIGHT: 344px" align="center" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td class="theader" style="PADDING-TOP: 4px">Auto Remove the following URLs:
											</td>
										</tr>
										<tr><!--don't valign to top-->
											<td>
												<table style="WIDTH: 312px" align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td style="PADDING-RIGHT: 10px; PADDING-LEFT: 10px; WIDTH: 308px; TEXT-ALIGN: center">
															<textarea rows="5" readonly wrap="off" cols="45">
END;
foreach ($aRemove as $value) {
	echo $value . "\n";
}
echo <<<END
</textarea>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr style="HEIGHT: 7px">
											<td>
											</td>
										</tr>
										<tr>
											<td style="PADDING-RIGHT: 25px; PADDING-LEFT: 20px">
												<label>Add to List:</label>
												<div>
													<label>URL:</label>
													<input style="WIDTH: 100%" name="removeurl"><br>
													<input type="submit" style="MARGIN-TOP: 4px" value="Insert" name="removelist">
												</div>
											</td>
										</tr>
										<tr style="HEIGHT: 7px">
											<td>
											</td>
										</tr>
										<tr>
											<td style="PADDING-RIGHT: 20px; PADDING-LEFT: 20px">
												<label>Remove from List:</label>
												<div>
													<select style="WIDTH: 100%" size="2" name="url-reset">

END;
foreach ($aRemove as $value) {
	echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t" . '<option>' . $value . '</option>' . "\n";
}
echo <<<END
													</select>
													<input type="submit" style="MARGIN-TOP: 4px; MARGIN-BOTTOM: 10px" value="Remove" name="ureset">
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr><!--bottom row right-->
							<tr style="HEIGHT: 6px" valign="top">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table style="WIDTH: 319px; HEIGHT: 34px" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td>
												<table align="center" cellspacing="1" cellpadding="2" border="0">
													<tr>
														<td style="WIDTH: 125px" class="acenter"><strong>Previous&nbsp;Options</strong>
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 71px">
															<input type="submit" value="Previous" name="menu_1">
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr style="HEIGHT: 4px">
								<td>
								</td>
							</tr>
							<tr>
								<td>
									<table style="WIDTH: 319px; HEIGHT: 34px" cellspacing="1" cellpadding="2" bgcolor="#dddddd" border="0">
										<tr>
											<td>
												<table align="center" cellspacing="0" cellpadding="0" border="0">
													<tr>
														<td style="WIDTH: 24px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 125px" class="acenter"><strong>Return&nbsp;to&nbsp;Main&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong>
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 71px">
															<input type="submit" value=" Return " name="return">
															<input type="hidden" value="
END;
												echo htmlentities($sCurrentTag); echo <<<END
" name="tag">
														</td>
														<td style="WIDTH: 2px"><!-- spacer here -->
														</td>
														<td style="WIDTH: 20px"><!-- spacer here -->
															<div class="returnmain">&nbsp;
															</div>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table></form>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

END;
} //END OF 'if ($inMenu2)'
?>
<?php
if ($outputzip) {
	echo '<script type=\'text/javascript\'>';
	echo 'setTimeout(function(){window.location = "./' . $target_zip . '"}, 1000);';
	//echo 'window.location = "./' . $target_zip . '"';
	echo '</script>' . "\n";
}
?>
</body>
</html>
