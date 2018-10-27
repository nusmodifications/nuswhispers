import withRedux from 'next-redux-wrapper';
import BaseApp, { Container } from 'next/app';
import React from 'react';
import { Provider } from 'react-redux';
import { Store } from 'redux';
import { makeStore } from '../store';

interface AppProps {
  Component: React.Component;
  store: Store;
}

class App extends BaseApp<AppProps> {
  render() {
    const { Component, store } = this.props;

    return (
      <Container>
        <Provider store={store}>
          <Component />
        </Provider>
      </Container>
    );
  }
}

export default withRedux(makeStore)(App);
