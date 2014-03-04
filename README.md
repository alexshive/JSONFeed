JSONFeed
========

Coppermine is a great tool for uploading files but is falling behind in responsive front end UI. I built this plugin based off xfeed. This currently works for version 1.5+

### Install 

- Install via the Plugin Manager. 

### How to use it

Access to the JSON via: `http://coppermineurl/index.php?file=JSONFeed/json`

### Parameters

- Access categories via **cid**
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&cid=10`
- View albums with **aid**
 	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&aid=10`
- Page (will append **'more=true'** if there are more photos to display)
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&aid=2&page=2`
- Total photos per page (overrides default amount)
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&aid=2&totalphotos=10&page=2`
- Total albums per page (overrides default amount)
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&aid=2&totalalbums=10&page=2`
- Total random photos (overrides default amount)
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&aid=2&totalrandom=10`
	

#### Optional Parameters

- Debug (must equal to 1, using *true* won't work)
	- prettify JSON and view queries
	- Ex: `http://coppermineurl/index.php?file=JSONFeed/json&debug=1`
