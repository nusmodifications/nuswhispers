import React from 'react';
import { maxContainerWidth, primaryColor } from '../styles';

export default () => (
  <style jsx global>{`
    body {
      background-color: #f8f9fa;
      color: #343a40;
      font-family: 'Rubik', -apple-system, BlinkMacSystemFont, 'Open Sans',
        'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 1rem;
      line-height: 1.5rem;
      margin: 0;
      padding: 0;
      -webkit-font-smoothing: subpixel-antialiased;
      -moz-osx-font-smoothing: greyscale;
    }

    a,
    a:visited {
      color: ${primaryColor};
      cursor: pointer;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    b,
    strong {
      font-weight: 500;
    }

    .container {
      display: flex;
      margin: 0 auto;
      max-width: ${maxContainerWidth};
      width: 100%;
    }
  `}</style>
);
