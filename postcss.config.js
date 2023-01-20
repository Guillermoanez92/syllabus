module.exports = {
	syntax: "postcss-scss",
	plugins: {
		"postcss-import": {},
		"autoprefixer": {
			overrideBrowserslist: ["last 2 versions","ie 11"]
		}
	}
};
