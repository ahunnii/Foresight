//Twitter Parsers
String.prototype.parseURL = function() {
	return this.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~\?\/.=]+/g, function(url) {
		return url.link(url);
	});
};
String.prototype.parseUsername = function() {
	return this.replace(/[@]+[A-Za-z0-9-_]+/g, function(u) {
		var username = u.replace("@","");
		return u.link("http://twitter.com/" + username);
	});
};
String.prototype.parseHashtag = function() {
	return this.replace(/[#]+[A-Za-z0-9-_]+/g, function(t) {
		var tag = t.replace("#", "%23");
		return t.link("http://search.twitter.com/search?q=" + tag);
	});
};
function parseDate(str) {
    var v=str.split(' ');
    return new Date(Date.parse(v[2] + " " + v[1] + ", " + v[3] + " " + v[4] + " UTC"));
} 

function timeFormat(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours >= 12 ? 'pm' : 'am';
	hours = hours % 12;
	hours = hours ? hours : 12;
	minutes = minutes < 10 ? '0' + minutes : minutes;
	var strTime = hours + ':' + minutes + ampm;
	return strTime;
}

function loadLatestTweet(twitterFeed, callback){
	var numTweets = 1;
    //var _url = 'https://api.twitter.com/1/statuses/user_timeline/' + twitterFeed + '.json?callback=?&count=' + numTweets + '&include_rts=1';
	var _url = 'http://search.twitter.com/search.json?q=from:' + twitterFeed + '&callback=?';
	
	$.ajax(_url, {
		'dataType'	: 'json',
	})
	.done(function(data){
		//console.log('Twitter Feed: %o', data);
		
		var results = data.results;
		for(var i = 0; i < results.length; i++) {
            var tweet = results[i].text;
			var created = parseDate(results[i].created_at);
            var createdDate = (created.getMonth() + 1) + '-' + created.getDate() + '-' + created.getFullYear() + ' at ' + timeFormat(created);
            tweet = tweet.parseURL().parseUsername().parseHashtag();
            tweet += '<div class="tag"><a href="https://twitter.com/#!/' + twitterFeed + '" target="_blank" class="black">@' + twitterFeed + '</a></div><div class="date"><a href="https://twitter.com/#!/' + twitterFeed + '/status/'+results[i].id_str+'">'+createdDate+'</a></div>';
            $("#TwitterFeed").append('<div class="TwitterPost"><p>' + tweet + '</p></div>');
        }
    })
	.always(function() {
		if (callback)
			callback();
	});
}