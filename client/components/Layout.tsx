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
    <div>
      <main>{children}</main>
      <Sidebar />
      <style jsx>{`
        div {
          display: flex;
          margin: 1rem auto;
          max-width: 1000px;
        }

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
