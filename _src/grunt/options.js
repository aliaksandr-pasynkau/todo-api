module.exports = function(){
	var pkg = this.file.readJSON('package.json');

	return {
		_: require('underscore'),
		cacheKey: Date.now(),
		pkg: pkg,
		liveReload: {
			port: 35729,
			fileUrl: '//www.'+pkg.name+':35729/livereload.js'
		}
	};
};