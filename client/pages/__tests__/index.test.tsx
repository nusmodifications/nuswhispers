/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import Index from '../index';

describe('Index', () => {
  it('works', () => {
    const index = shallow(<Index />);
    expect(index.find('div').text()).toEqual('Hello World!');
  });
});
