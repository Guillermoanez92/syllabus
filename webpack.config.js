/* --- CDN Configuration ---
 *
 *  Make sure to fill these out with the folder/bucket IDs from the CDN
 *  Having this enabled will REQUIRE a cdnkey.json file
 */
// let bucketId = "prismic-php-development";
// let projectFolder = "prismic-php";
// let projectId = 782062877748;

/* --- Integration Config ---
 *
 *  Please only include at most: one CMS and one eCommerce
 *  Integrations are contained in the lib folder
 *
 *  cores: php-slim
 *
 *  ecommerce:
 *    platform: shopify, bigcommerce
 *    subscription: recharge
 *
 *  content_management: prismic, contentful
 *
 *  data: elasticsearch
 *
 *  framework: prismic-php, react
 */
let integrations = {
	core: "php-slim",
	// ecommerce: {
		// 	platform: "shopify",
		// subscription: "recharge"
	// },
	content_management: "prismic",
	framework: "prismic-php"
};

/* --- CopyWebpackPlugin Array ---
 *
 *  Array of copied files setup based on "integrations"
 *
 */
let copyWebpackPluginArray = [
	/* --- Core - required --- */
	{
		from: "./lib/core/" + integrations.core,
		to: "../../lib/core/" + integrations.core,
		force: true
	}
];

/* --- CopyWebpackPlugin - framework --- */
if(integrations.framework){

	let copyWebpackPluginFramework = [
		{
			from: "./lib/framework/" + ((integrations.framework === "react") ? integrations.framework + "/src" : integrations.framework) + "/images",
			to: "./images",
			force: true,
			ignore: [
				".gitkeep"
			]
		},
		{
			from: "./lib/framework/" + ((integrations.framework === "react") ? integrations.framework + "/src" : integrations.framework) + "/fonts",
			to: "./fonts",
			force: true,
			ignore: [
				".gitkeep"
			]
		},
		{
			from: "./lib/framework/" + integrations.framework + "/templates",
			to: "../../app/views",
			force: true,
			ignore: [
				".gitkeep"
			]
		}
	];

	copyWebpackPluginArray = copyWebpackPluginArray.concat(copyWebpackPluginFramework);
}

/* --- CopyWebpackPlugin - data --- */
if(integrations.data){

	let copyWebpackPluginData = [
		{
			from: "./lib/data/" + integrations.data,
			to: "./lib/data/" + integrations.data,
			force: true
		}
	];

	copyWebpackPluginArray = copyWebpackPluginArray.concat(copyWebpackPluginData);
}

/* --- CopyWebpackPlugin - ecommerce --- */
if(integrations.ecommerce){

	/* --- Get our platform --- */
	if(integrations.ecommerce.platform){

		let copyWebpackPluginEcommerce = [
			{
				from: "./lib/ecommerce/platform/" + integrations.ecommerce.platform,
				to: "../../lib/ecommerce/platform/" + integrations.ecommerce.platform,
				force: true
			},
			{
				from: "./lib/ecommerce/*.php",
				to: "../..",
				force: true
			}
		];

		copyWebpackPluginArray = copyWebpackPluginArray.concat(copyWebpackPluginEcommerce);
	}

	/* --- Get our subscription --- */
	if(integrations.ecommerce.subscription){

		let copyWebpackPluginSubscription = [
			{
				from: "./lib/ecommerce/subscription/" + integrations.ecommerce.subscription,
				to: "../../lib/ecommerce/subscription/" + integrations.ecommerce.subscription,
				force: true
			}
		];

		copyWebpackPluginArray = copyWebpackPluginArray.concat(copyWebpackPluginSubscription);
	}
}

/* --- CopyWebpackPlugin - content_management --- */
if(integrations.content_management){

	let copyWebpackPluginContent = [
		{
			from: "./lib/content/" + integrations.content_management,
			to: "../../lib/content/" + integrations.content_management,
			force: true
		},
		{
			from: "./lib/content/*.php",
			to: "../..",
			force: true
		}
	];

	copyWebpackPluginArray = copyWebpackPluginArray.concat(copyWebpackPluginContent);
}

/* global __dirname */
const webpack = require("webpack");
const path = require("path");
const entry = require("webpack-glob-entry");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCssAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const CopyWebpackPlugin = require("copy-webpack-plugin");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const FriendlyErrorsPlugin = require("friendly-errors-webpack-plugin");
const StyleLintPlugin = require("stylelint-webpack-plugin");
const GenerateJSONPlugin = require("generate-json-webpack-plugin");
const WebpackGoogleCloudStoragePlugin = require("webpack-google-cloud-storage-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

function recursiveIssuer(m) {

	if (m.issuer) {
		return recursiveIssuer(m.issuer);
	} else if (m.name) {
		return m.name;
	} else {
		return false;
	}
}

let compiledPath = "../../../compiled/";

let entryObject = {
	"code/main.js": [
		"./lib/framework/" + integrations.framework + "/js/main.js"
	],
	"code/image-load.js": [
		"./lib/framework/" + integrations.framework + "/js/_worker/workers/image-load.js"
	],
	"../../../compiled/init": [
		"./lib/framework/" + integrations.framework + "/css/critical/init.scss"
	],
	"../../../compiled/fonts": [
		"./lib/framework/" + integrations.framework + "/css/_fonts.css"
	],
	"../../../compiled/main": [
		"./lib/framework/" + integrations.framework + "/css/main.scss"
	]
};

if(integrations.framework === "react"){

	entryObject = {
		"code/main.js": [
			"./lib/framework/" + integrations.framework + "/src/js/app.js"
		],
		"../../../compiled/main": [
			"./lib/framework/" + integrations.framework + "/src/css/home.scss"
		]
	}
}

module.exports = {
	mode: "production",
	entry: entryObject,
	output: {
		path: path.resolve(__dirname, "./web/site/assets/"),
		filename: "[name]",
		chunkFilename: compiledPath + "[id].chunk"
	},
	resolve: {
		extensions: [".webpack.js", ".web.js", ".js", ".jsx"]
	},
	optimization: {
		splitChunks: {
			cacheGroups: {
				init: {
					name: "code/_init.css",
					test: (m,c,entry = "../../../compiled/init") => m.constructor.name === "CssModule" && recursiveIssuer(m) === entry,
					chunks: "initial",
					enforce: true
				},
				fonts: {
					name: "code/_fonts.css",
					test: (m,c,entry = "../../../compiled/fonts") => m.constructor.name === "CssModule" && recursiveIssuer(m) === entry,
					chunks: "initial",
					enforce: true
				},
				main: {
					name: "code/main.css",
					test: (m,c,entry = "../../../compiled/main") => m.constructor.name === "CssModule" && recursiveIssuer(m) === entry,
					chunks: "initial",
					enforce: true
				},
				default: false
			}
		},
		minimizer: [
			new UglifyJsPlugin({
				test: [/\.js$/i],
				sourceMap: false,
				uglifyOptions: {
					ecma: 8,
					compress: {
						warnings: false
					}
				}
			}),
			new OptimizeCssAssetsPlugin({
				assetNameRegExp: /\.css$/i,
				cssProcessor: require("cssnano"),
				cssProcessorOptions: {
					preset: [
						"advanced",
						{
							autoprefixer: {
								add: true
							},
							reduceIdents: false,
							zindex: {
								exclude: false
							}
						}
					]
				}
			})
		]
	},
	node: {
		fs: 'empty'
	},
	module: {
 		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				use: [
					{
						loader: "babel-loader",
						options: {
							presets: [
								[
									"@babel/preset-env"
								],
								[
									"@babel/preset-react"
								]
							],
							plugins: [
								[
									"@babel/plugin-proposal-decorators", {
										"legacy": true
									}
								],
								[
									"@babel/plugin-syntax-dynamic-import"
								],
								[
									"@babel/plugin-proposal-class-properties", {
										"loose": true
									}
								],
								[
									"@babel/plugin-transform-runtime", {
										"regenerator": true
									}
								]
							]
						}
					},
					{
						loader: "eslint-loader"
					}
		        ]
			},
			{
				test: /\.(png|woff|woff2|eot|ttf|svg)$/,
				loader: "url-loader?limit=100000"
			},
			{
				test: /\.(sa|sc|c)ss$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: "css-loader",
						options: {
							importLoaders: 1,
							minimize: true
						}
					},
					{
						loader: "postcss-loader",
						options: {
							config: {
								path: path.resolve(__dirname, "./postcss.config.js")
							}
						}
					},
					{
						loader: "sass-loader"
					}
				]
			}
		]
	},
	externals: {
		appData: "appData"
	},
	plugins: [
		new CleanWebpackPlugin(),
		new MiniCssExtractPlugin({
			filename: "[name]"
		}),
		new CopyWebpackPlugin(copyWebpackPluginArray),
		/* --- Comment out this plugin if you want to run without config --- *
		new WebpackGoogleCloudStoragePlugin({
			directory: "./web/site/assets",
			include: [/\.(js|css)$/],
			storageOptions: {
				projectId: projectId,
				keyFilename: "./cdnkey.json"
			},
			uploadOptions: {
				bucketName: bucketId,
				destinationNameFn: file => projectFolder + "/assets/" + file.name
			}
		}),
		/* --- End comment --- */
		new GenerateJSONPlugin("../build.json", {
			date: new Date().getTime(),
			// cdnUrl: bucketId + "/" + projectFolder + "/assets/",
			lib: integrations
		}),
		new StyleLintPlugin(),
		new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
		new webpack.NoEmitOnErrorsPlugin(),
		new FriendlyErrorsPlugin()
	]
};
