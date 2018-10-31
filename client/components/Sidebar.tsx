import React from 'react';
import { columnPadding } from '../styles';

export default () => (
  <div>
    <style jsx>{`
      div {
        flex: 1;
        padding: ${columnPadding};
      }
    `}</style>
  </div>
);
