//ssbme.js
//Online/offline logic needs to be revisited
//30 May 2018

var myObject = {};
var complete = false;
var flag_ = true;

function transferComplete(f) {
  complete = f;
}

function writeParameter(r) {
  document.getElementById("loctxt").value = r.myObject.myKey;
  myObject.myKey = r.myObject.myKey; //myObject.myKey will be used later
  var xhr = new XMLHttpRequest();
  xhr.onload = function(){
	var flag = false;
	var selTag = document.getElementById("tag");
	var selTag_ = document.getElementById("tag_");
	selTag.options.length = 0; //remove previous options in case you're transitioning pages
	selTag_.options.length = 0; //ditto
	var remoteDocument = this.responseXML;
	var form = remoteDocument.forms;
	try {var remoteOption = form.item(0).firstElementChild.nextElementSibling.firstElementChild;}
	catch(e) {var remoteOption = false;}

	while(remoteOption){
		var option = document.createElement("option");
		var option_ = document.createElement("option"); //forget cloning!
		option_.value = option.value = remoteOption.innerHTML;
		if (r.myObject.myTag == remoteOption.innerHTML) option_.selected = true;
		option_.text = option.text = remoteOption.innerHTML;
		selTag.add(option);
		selTag_.add(option_);
		if (option.text == 'no-tags') flag = true;
 		remoteOption = remoteOption.nextElementSibling;
	}

	transferComplete(flag);
	if (flag) {
		document.getElementById("utext").value = 'no-tags';
	}
  };
  xhr.open("GET", r.myObject.myKey);
  xhr.responseType = "document";
  xhr.send();
}

function saveOptions(e) {
  var injectedCode = 'browser.runtime.sendMessage({reply: window.location.href});';
  browser.tabs.executeScript({code: injectedCode});
  e.preventDefault();
}

function restoreOptions() {
  browser.storage.local.get('myObject').then(writeParameter);
}

function handleMessage(request, sender, sendResponse) {
  var s, t;
  s = request.reply.split("?");
  t = s[0];
  myObject.myKey = t;
  browser.storage.local.set({myObject}).then(restoreOptions);
  sendResponse(null);
}

function dBtnFunc() {
  if (!complete) {document.getElementById("warning").style.display = "initial"; document.getElementById("uwarning").style.display = "none"; document.getElementById("suwarning").style.display = "none"; return;}
  if (!window.navigator.onLine) alert('          Cannot complete while offline!        ');

  var tag = document.getElementById("tag").options[document.getElementById("tag").selectedIndex].value;
  var url = myObject.myKey + '?tag=' + tag;

  var xhr = new XMLHttpRequest();
  xhr.onload = function(){
    var i, j, k, el, elements;
    var remoteDocument = this.responseXML;

    function fSuccess(p) { //function within a function
      for (i = 1; i < el; i++) {
        console.log(j = elements[i].innerHTML);
        console.log(k = elements[i].nextElementSibling.firstElementChild.innerHTML);
        browser.bookmarks.create({title: j, url: k, parentId: "menu________"});
      }
    }

    function fFail(error) { //function within a function
      for (i = 0; i < el; i++) {
        console.log(j = elements[i].innerHTML);
        console.log(k = elements[i].nextElementSibling.firstElementChild.innerHTML);
        browser.bookmarks.create({title: j, url: k});
      }
    }

    function fSuccess_(p) { //function within a function
      for (i = 1; i < el; i++) {
        console.log(j = elements[i].firstElementChild.innerText);
        console.log(k = elements[i].firstElementChild.href);
        browser.bookmarks.create({title: j, url: k, parentId: "menu________"});
      }
    }

    function fFail_(error) { //function within a function
      for (i = 0; i < el; i++) {
        console.log(j = elements[i].firstElementChild.innerText);
        console.log(k = elements[i].firstElementChild.href);
        browser.bookmarks.create({title: j, url: k}).then(fSuccess, fFail);
      }
    }

    elements = remoteDocument.getElementsByClassName('bmLabel');
    //console.log(elements);
    el = elements.length;
    if (el > 0){
      console.log(j = elements[0].innerHTML);
      console.log(k = elements[0].nextElementSibling.firstElementChild.innerHTML);
      browser.bookmarks.create({title: j, url: k, parentId: "menu________"}).then(fSuccess, fFail);
    } else {
      elements = remoteDocument.getElementsByClassName('bmLink');
      el = elements.length;
      console.log(j = elements[0].firstElementChild.innerText);
      console.log(k = elements[0].firstElementChild.href);
      browser.bookmarks.create({title: j, url: k, parentId: "menu________"}).then(fSuccess_, fFail_);
    }
  }
  xhr.open("GET", url);
  xhr.responseType = "document";
  xhr.send();
}

function uBtnFunc() {
  if (!complete) {document.getElementById("uwarning").style.display = "initial"; document.getElementById("warning").style.display = "none"; document.getElementById("suwarning").style.display = "none"; return;}
  if (!window.navigator.onLine) alert('          Cannot complete while offline!        ');

  function onError(error) {
    console.log(`Error: ${error}`);
  }

  function onFulfilled(bookmarkItems) {
    var url = myObject.myKey;
    var utext = document.getElementById("utext").value;

    function inBackground(backgroundPage){
      backgroundPage.onFulfilled_(bookmarkItems, url, utext);
    }
    var bPromise = browser.runtime.getBackgroundPage();
    bPromise.then(inBackground, onError);

    return;
  }

  function onRejected(error) {
    alert('Could Not Complete!');
  }

  var searching = browser.bookmarks.search({});
  searching.then(onFulfilled, onRejected);
}

function fixedEncodeURIComponent(str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
    return '%' + c.charCodeAt(0).toString(16);
  }).replace(/%20/g, "+");
}

function suBtnFunc() {
  if (!complete) {document.getElementById("suwarning").style.display = "initial"; document.getElementById("warning").style.display = "none"; document.getElementById("uwarning").style.display = "none"; return;}
  if (!window.navigator.onLine) alert('          Cannot complete while offline!        ');

  var tag_ = document.getElementById("tag_").options[document.getElementById("tag_").selectedIndex].value;
  var url = myObject.myKey;

  function onGotInfo(tabInfo) {
    myObject.myTag = tag_;
    browser.storage.local.set({myObject});
    var t;
    t = tabInfo.url;

    function getDescriptionContent(d) { //function within a function
       var metas = d.getElementsByTagName('meta'); 
       for (var i=0; i<metas.length; i++) { 
          if (metas[i].getAttribute("name") == "description") { 
             return metas[i].getAttribute("content"); 
          } 
       } 
       return ""; //if not found
    }

    var getMetatag = new XMLHttpRequest();
    getMetatag.onload = function() {
      var remoteDocument = this.responseXML;
      var description = getDescriptionContent(remoteDocument);
      if (description == '') description = fixedEncodeURIComponent(tabInfo.title);
      //console.log(description);

      var fullUrl = url + '?label=' + fixedEncodeURIComponent(tabInfo.title) + '&url=' + fixedEncodeURIComponent(t) + '&description=' + description + '&tags=' + tag_ + '&tag=no-tags&submit=Add+Bookmark&menu_1=menu_1';
      //console.log(fullUrl);

      var xhr = new XMLHttpRequest();

      xhr.onload = function() { //function within a function
        var remoteDocument = this.responseXML;
        var form = remoteDocument.forms;
        try {var remoteOption = form.item(2).firstElementChild.firstElementChild.firstElementChild.firstElementChild.firstElementChild.firstElementChild.firstElementChild.nextElementSibling.firstElementChild.firstElementChild.firstElementChild.firstElementChild.firstElementChild.nextElementSibling.firstElementChild[0].innerHTML;}
        catch(e) {var remoteOption = false;} //if the form element is not present an exception is thrown
        if (remoteOption == 'no-tags') {document.getElementById("suwarning").innerHTML = "Upload Successful!"; document.getElementById("suwarning").style.color = 'blue'; document.getElementById("suwarning").style.display = "initial";}
        else  {document.getElementById("suwarning").innerHTML = "Upload Unsuccessful!"; document.getElementById("suwarning").style.color = 'blue'; document.getElementById("suwarning").style.display = "initial";}
      }
      xhr.open("GET", fullUrl);
      xhr.responseType = "document";
      xhr.send();
    } 
    getMetatag.open("GET", t);
    getMetatag.responseType = "document";
    getMetatag.send();
  }

  function onError(error) {
      alert('Could Not Complete!');
  }

  function getInfo(tabs) {
    if (tabs.length > 0) {
      var gettingInfo = browser.tabs.get(tabs[0].id);
      gettingInfo.then(onGotInfo, onError);
    }
  }

  var currentTab = browser.tabs.query({currentWindow: true, active: true});
  currentTab.then(getInfo, onError);
}

function clearText() {
  if (flag_) {document.getElementById("utext").value = ''; flag_ = false;}
}

document.addEventListener('DOMContentLoaded', restoreOptions);
document.querySelector("form").addEventListener("submit", saveOptions);
document.getElementById("downloadBtn").addEventListener("click", dBtnFunc);
document.getElementById("uploadBtn").addEventListener("click", uBtnFunc);
document.getElementById("suploadBtn").addEventListener("click", suBtnFunc);
browser.runtime.onMessage.addListener(handleMessage);
document.getElementById("utext").addEventListener("click", clearText);




