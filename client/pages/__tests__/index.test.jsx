/* eslint-env jest */
import { shallow } from 'enzyme';
import React from 'react';
import Index from '../index.tsx';

describe('Index', () => {
  it('works', () => {
    const index = shallow(<Index />);
    expect(index.find('div').text()).toEqual('Hello World!');
  });
});
