import React from 'react';
import { columnPadding } from '../styles';
import Brand from './Brand';

export default () => (
  <header>
    <div className="container">
      <Brand />
    </div>
    <style jsx>{`
      header {
        background-color: #fff;
      }

      .container {
        box-sizing: border-box;
        padding: 1.25rem ${columnPadding};
      }
    `}</style>
  </header>
);
