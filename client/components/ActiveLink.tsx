import Link, { LinkProps } from 'next/link';
import { withRouter, WithRouterProps } from 'next/router';
import React, { Children } from 'react';

const ActiveLink = ({
  router,
  children,
  ...props
}: LinkProps & WithRouterProps) => (
  <Link {...props}>
    {React.cloneElement(Children.only(children), {
      className: router.asPath === props.href ? `active` : null,
    })}
  </Link>
);

export default withRouter<LinkProps>(ActiveLink);
