const Path = require("path");
const PATHS = {
    MODULES: 'node_modules',
    FILES_PATH: '../',
    ROOT: Path.resolve(),
    SRC: Path.resolve('client/src'),
    DIST: Path.resolve('client/dist'),
};

module.exports = {
    mode: 'production',
    target: 'web',
    entry: {
        search: PATHS.SRC + '/javascript/search.js'
    },
    output: {
        filename: 'javascript/[name].min.js',
        path: PATHS.DIST,
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /(node_modules)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', { targets: "defaults" }]
                        ],
                    }
                }
            }
        ]
    }
}
