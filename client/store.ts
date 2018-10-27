import { applyMiddleware, compose, createStore } from 'redux';
import thunkMiddleware from 'redux-thunk';
import reducers from './reducers';

export const makeStore = (state = {}) => {
  const middlewares = [thunkMiddleware];

  /* istanbul ignore next */
  if (process.env.NODE_ENV === 'development') {
    // Add redux-logger middleware in development environments.
    middlewares.push(require('redux-logger').createLogger());
  }

  return createStore(reducers, state, compose(applyMiddleware(...middlewares)));
};
