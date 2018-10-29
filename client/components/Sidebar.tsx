import React from 'react';
import { columnPadding } from '../styles';

export default () => (
  <div>
    Sidebar
    <style jsx>{`
      div {
        flex: 1;
        padding: ${columnPadding};
      }
    `}</style>
  </div>
);
