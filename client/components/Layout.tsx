import React, { Fragment } from 'react';
import { columnPadding, maxBreakpoint } from '../styles';
import Header from './Header';
import Sidebar from './Sidebar';

interface LayoutProps {
  children: React.ReactNode;
}

export default ({ children }: LayoutProps) => (
  <Fragment>
    <Header />
    <div className="container">
      <main>{children}</main>
      <Sidebar />
      <style jsx>{`
        main {
          flex: 2;
          padding: ${columnPadding};
        }

        @media (max-width: ${maxBreakpoint}) {
          div {
            flex-direction: column;
          }
        }
      `}</style>
    </div>
  </Fragment>
);
