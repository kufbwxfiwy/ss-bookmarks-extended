//background.js

function fixedEncodeURIComponent(str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, function(c) {
    return '%' + c.charCodeAt(0).toString(16);
  }).replace(/%20/g, "+");
}

function onFulfilled_(bookmarkItems, url, utext) {
  var limit = bookmarkItems.length;
  var fullUrl, j = 0;
  var xhr = new XMLHttpRequest();

  xhr.onload = function(){
    j++;
    if (j >= limit) return;
    if (bookmarkItems[j].title && bookmarkItems[j].url) {fullUrl = url + '?label=' + fixedEncodeURIComponent(bookmarkItems[j].title) + '&url=' + fixedEncodeURIComponent(bookmarkItems[j].url) + '&description=' + fixedEncodeURIComponent(bookmarkItems[j].title) + '&tags=' + fixedEncodeURIComponent(utext) + '&tag=&submit=Add+Bookmark&menu_1=menu_1';}
    else {fullUrl = url;}
    console.log(fullUrl); console.log(j); console.log(limit);
    xhr.open("GET", fullUrl);
    xhr.send();
  }

  fullUrl = url + '?label=' + fixedEncodeURIComponent(bookmarkItems[j].title) + '&url=' + fixedEncodeURIComponent(bookmarkItems[j].url) + '&description=' + fixedEncodeURIComponent(bookmarkItems[j].title) + '&tags=' + fixedEncodeURIComponent(utext) + '&tag=&submit=Add+Bookmark&menu_1=menu_1';
  xhr.open("GET", fullUrl);
  xhr.send();
}
