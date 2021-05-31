(function($) {
    'use strict';

var triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
triggerTabList.forEach(function (triggerEl) {
  var tabTrigger = new bootstrap.Tab(triggerEl)
  triggerEl.addEventListener('click', function (event) {
    event.preventDefault()
    tabTrigger.show()
  })
})  

var triggerEl = document.querySelector('#myTab button#home-tab')
bootstrap.Tab.getInstance(triggerEl).show() // Select tab by name 

var someTabTriggerEl = document.querySelector('#myTab')
var tab = new bootstrap.Tab(someTabTriggerEl)
tab.show()


$(document).ready(function() {})


})(jQuery);