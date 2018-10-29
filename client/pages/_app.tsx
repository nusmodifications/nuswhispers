import withRedux from 'next-redux-wrapper';
import BaseApp, { Container, NextAppContext } from 'next/app';
import React from 'react';
import { Provider } from 'react-redux';
import { Store } from 'redux';
import GlobalStyles from '../components/GlobalStyles';
import Layout from '../components/Layout';
import { makeStore } from '../store';

interface AppProps {
  Component: React.Component;
  store: Store;
}

class App extends BaseApp<AppProps> {
  static async getInitialProps({ Component, ctx }: NextAppContext) {
    return {
      pageProps: Component.getInitialProps
        ? await Component.getInitialProps(ctx)
        : {},
    };
  }

  render() {
    const { Component, pageProps, store } = this.props;
    return (
      <Container>
        <GlobalStyles />
        <Provider store={store}>
          <Layout>
            <Component {...pageProps} />
          </Layout>
        </Provider>
      </Container>
    );
  }
}

export default withRedux(makeStore)(App);
