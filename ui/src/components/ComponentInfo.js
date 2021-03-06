import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import FetchingCodeBlock from './FetchingCodeBlock';
import PropTypes from 'prop-types';
import cx from 'classnames';
import './ComponentInfo.css';

const ComponentInfo = ({ component, sample, used, className, controls }) => {
  return (
    <div className={cx('ComponentInfo', className)}>
      <div className="inner">
        <div className="controls">{controls}</div>
        <ComponentInfoInfo
          className="info"
          component={component}
          used={used}
          sample={sample}
        />
        <ComponentInfoCode
          className="code"
          component={component}
          sample={sample}
        />
      </div>
    </div>
  );
};
ComponentInfo.propTypes = {
  component: PropTypes.shape({}),
  sample: PropTypes.shape({}),
  used: PropTypes.array
};

export default ComponentInfo;

const ComponentInfoInfo = ({ component, sample, used }) => {
  return (
    <div className="info">
      <h3 className="component-name">{component.name}</h3>
      {component.metadata.description && (
        <ComponentInfoSection title="Description">
          {component.metadata.description}
        </ComponentInfoSection>
      )}
      {used.length > 0 && (
        <ComponentInfoSection title="Used">
          {used.map(p => (
            <Link key={p.id} to={`/component/${p.id}`}>
              {p.name}
            </Link>
          ))}
        </ComponentInfoSection>
      )}
    </div>
  );
};
ComponentInfoInfo.propTypes = {
  used: PropTypes.arrayOf(
    PropTypes.shape({
      id: PropTypes.string,
      name: PropTypes.string
    })
  ),
  sample: PropTypes.shape({}),
  component: PropTypes.shape({
    name: PropTypes.string.isRequired,
    metadata: PropTypes.shape({
      description: PropTypes.string
    })
  }).isRequired
};
ComponentInfoInfo.defaultProps = {
  used: []
};

const ComponentInfoSection = ({ title, children }) => (
  <section>
    <h4>{title}</h4>
    <p>{children}</p>
  </section>
);
ComponentInfoSection.propTypes = {
  title: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired
};

class ComponentInfoCode extends Component {
  constructor(props) {
    super(props);
    this.state = { language: null, src: null };
    this.switchMode = this.switchMode.bind(this);
  }
  componentDidMount() {
    this.resetSource();
  }
  componentDidUpdate(prevProps) {
    if (
      prevProps.sample !== this.props.sample ||
      prevProps.component !== this.props.component
    ) {
      this.resetSource();
    }
  }
  resetSource() {
    let state = { language: null, src: null };
    const { sample, component } = this.props;
    if (sample) {
      state.language = 'html';
      state.src = sample.source;
    } else if (component) {
      state.language = component.metadata.source_format;
      state.src = component.source;
    }
    this.setState(state);
  }
  switchMode(e) {
    this.setState({
      language: e.target.getAttribute('data-language'),
      src: e.target.getAttribute('data-src')
    });
  }
  render() {
    const { src, language } = this.state;
    const { sample, component } = this.props;

    return (
      <div className="code">
        {src && <FetchingCodeBlock src={src} language={language} />}
        <div className="button-group">
          {sample && (
            <a
              className={cx(
                'button',
                src === sample.source ? 'primary' : 'secondary'
              )}
              onClick={this.switchMode}
              data-src={sample.source}
              data-language={'html'}
            >
              HTML
            </a>
          )}
          {component && (
            <a
              className={cx(
                'button',
                src === component.source ? 'primary' : 'secondary'
              )}
              onClick={this.switchMode}
              data-src={component.source}
              data-language={component.metadata.source_format}
            >
              Raw
            </a>
          )}
        </div>
      </div>
    );
  }
}
ComponentInfoCode.propTypes = {
  sample: PropTypes.shape({
    source: PropTypes.string.isRequired
  }),
  component: PropTypes.shape({
    source: PropTypes.string.isRequired,
    metadata: PropTypes.shape({
      source_format: PropTypes.string
    })
  })
};
