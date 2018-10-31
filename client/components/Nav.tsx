import React from 'react';
import {
  columnPadding,
  gray100,
  gray200,
  gray500,
  gray900,
  maxBreakpoint,
  primaryColor,
  white,
} from '../styles';
import ActiveLink from './ActiveLink';

export default () => (
  <nav>
    <div>
      <ActiveLink href="/">
        <a>Featured</a>
      </ActiveLink>
      <ActiveLink href="/latest">
        <a>Latest</a>
      </ActiveLink>
      <ActiveLink href="/popular">
        <a>Popular</a>
      </ActiveLink>
      <ActiveLink href="/category/10">
        <a>Advice</a>
      </ActiveLink>
      <ActiveLink href="/category/5">
        <a>Funny</a>
      </ActiveLink>
      <ActiveLink href="/category/6">
        <a>Lost and Found</a>
      </ActiveLink>
      <ActiveLink href="/category/9">
        <a>Nostalgia</a>
      </ActiveLink>
      <ActiveLink href="/category/8">
        <a>Rant</a>
      </ActiveLink>
      <ActiveLink href="/category/7">
        <a>Romance</a>
      </ActiveLink>
    </div>
    <style jsx>{`
      nav {
        background: ${white};
        border-bottom: 2px solid ${gray200};
        border-top: 2px solid ${gray100};
        display: flex;
        height: 22px;
        overflow: hidden;
        padding: ${columnPadding};
      }

      div {
        display: flex;
        height: 150%;
        justify-content: center;
        margin: 0 auto;
        overflow-x: scroll;
        overflow-y: hidden;
        padding-bottom: 1rem;
        white-space: nowrap;
        width: 100%;
      }

      div :global(a) {
        display: block;
        color: ${gray500};
        font-size: 0.75rem;
        font-weight: 500;
        margin: 0 1rem;
        transition: color 0.15s;
        text-transform: uppercase;
      }

      div :global(a.active) {
        color: ${primaryColor};
      }

      div :global(a:first-child) {
        margin-left: 0;
      }

      div :global(a:hover) {
        color: ${gray900};
        text-decoration: none;
      }

      @media (max-width: ${maxBreakpoint}) {
        div {
          justify-content: left;
        }
      }
    `}</style>
  </nav>
);
