const { configure } = require('enzyme');
const Adapter = require('enzyme-adapter-react-16');
const { setConfig } = require('next/config');

// Support Enzyme for React 16.
configure({ adapter: new Adapter() });

// Make sure you can use "publicRuntimeConfig" within tests.
setConfig(require('./next.config'));
