/**
 * Props for this custom heading tag handler goes to Saman via https://stackoverflow.com/a/56411377/423330
 */

import React from 'react';

const elements = {
  h1: 'h1',
  h2: 'h2',
  h3: 'h3',
  h4: 'h4',
  h5: 'h5',
  h6: 'h6',
  p: 'p',
  div: 'div',
};

function HeadingTag( { type = 'h2', children, ...props } ) {
  return React.createElement(
    elements[type] || elements.h2,
    props,
    children
  );
}
export default HeadingTag;
