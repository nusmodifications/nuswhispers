import React from 'react';
import { primaryColor } from '../styles';

export default () => (
  <style jsx global>{`
    body {
      background-color: #f5f5f5;
      color: #111;
      font-family: -apple-system, BlinkMacSystemFont, 'Open Sans', 'Segoe UI',
        'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 0.9375rem;
      line-height: 1.4rem;
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: subpixel-antialiased;
      -moz-osx-font-smoothing: greyscale;
    }

    a:link {
      color: ${primaryColor};
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  `}</style>
);
