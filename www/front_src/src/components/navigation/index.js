import React, { Component } from "react";

import logo from "../../img/centreon.png";
import miniLogo from "../../img/centreon-logo-mini.svg";

import { Link, withRouter } from "react-router-dom";
import axios from "../../axios";

import routeMap from "../../route-maps/route-map";

import { Translate } from 'react-redux-i18n';
import { setNavigation } from "../../redux/actions/navigationActions";
import { connect } from "react-redux";

import { updateTooltip } from '../../redux/actions/tooltipActions';

class NavigationComponent extends Component {
  navService = axios("internal.php?object=centreon_menu&action=menu");

  clickTimeout = null;
  doubleClicked = false;

  state = {
    active: false,
    initiallyCollapsed: false,
    selectedMenu: {},
    menuItems: [],
    focused: false
  };

  UNSAFE_componentWillMount = () => {
    const { setNavigation } = this.props

    this.navService.get().then(({ data }) => {

      // store allowed topologies in redux (useful to get acl information in other components)
      setNavigation(data);

      // provide data in the state (render menu)
      this.setState({
        menuItems: data,
        selectedMenu: Object.values(data)[0]
      });
    })
  };
// toggle between icons menu and details menu
  toggleNavigation = () => {
    const { active } = this.state;
    this.setState({
      active: !active
    });
  };
  // handle double click on level 1
  handleDirectClick = (levelOneKey, levelOneProps) => {
    clearTimeout(this.clickTimeout)
    this.doubleClicked = true
    const urlOptions = levelOneKey.slice(1) +
      (levelOneProps.options !== null ? levelOneProps.options : '')
    this.goToPage(
      routeMap.module + "?p=" + urlOptions,
      levelOneKey
    )
  }
   //display/hide level 2
   collapseLevelTwo = index => {
    this.clickTimeout = setTimeout(() => {
        let { menuItems } = this.state;

        Object.keys(menuItems).forEach(key => {
          menuItems[key].toggled = key === index ?
            !menuItems[index].toggled : false;
        });
        this.setState({
          active: true,
          menuItems
        });
    }, 200);
  };

  // display/hide level 3
  collapseLevelThree = (levelOneKey, levelTwoKey) => {
    let { menuItems } = this.state;

    Object.keys(menuItems[levelOneKey].children).forEach(subKey => {
      if (subKey === levelTwoKey) {
        menuItems[levelOneKey].children[subKey]["collapsed"] = !menuItems[levelOneKey].children[subKey]["collapsed"];
      } else {
        menuItems[levelOneKey].children[subKey]["collapsed"] = false;
      }
    });

    this.setState({
      menuItems
    });
  };

  //open menu
  openNavigation = () => {
    this.setState({
      active: true
    });
  };

  //close menu
  closeNavigation = () => {
    this.setState({
      active: false
    });
  };

  //active current element
  activeCurrentElement = (e) => {
    e.currentTarget.className += ' active';
  }

  resetActiveElements = () => {
    let targetElement = document.getElementsByClassName('third-level');
    let targetElementClass = targetElement.classList;
    console.log(targetElement);
    console.log(targetElementClass);
    //targetElementClass.remove("active");

  }

  manageActiveItems = () => {
    this.resetActiveElements(e);
    this.activeCurrentElement();
  }


  // activate level 1 (display colored menu)
  activateTopLevelMenu = index => {
    let { menuItems } = this.state;

    Object.keys(menuItems).forEach(key => {
      menuItems[key].active = (key === index);
    })

    this.setState({
      menuItems
    });
  };

  // navigate to the page
  goToPage = (route, topLevelIndex) => {
    const { history } = this.props;
    this.activateTopLevelMenu(topLevelIndex);
    history.push(route);
  };

  // hide tooltip for the first-level folded menu items
  mouseLeftTheMenu = event => {
    const { updateTooltip } = this.props;
    updateTooltip({
      toggled: false
    });
  };

  // show tooltip for the first-level folded menu items by setting toggled to true
  // updating the x, y properties of tooltip in order to display it on client cursor position
  // show related label by setting label to label
  mouseIsMovingOverTheMenu = (label, {  clientY }) => {
    const { updateTooltip } = this.props;
    updateTooltip({
      toggled: true,
      x: 50,
      y: clientY,
      label
    });
  };

  render() {
    const { active, menuItems } = this.state;
    const pageId = this.props.history.location.search.split("p=")[1];

    return (
      <nav
        className={`sidebar${active ? " active" : ""}`}
        id="sidebar"
      >
        <div
          className="sidebar-inner"
        >
          <div className="sidebar-logo" onClick={this.toggleNavigation}>
            <span>
              <img
                className="sidebar-logo-image"
                src={logo}
                width="254"
                height="57"
                alt=""
              />
            </span>
          </div>
          <div className="sidebar-logo-mini" onClick={this.toggleNavigation} >
            <span>
              <img
                className="sidebar-logo-mini-image"
                src={miniLogo}
                width="23"
                height="21"
                alt=""
              />
            </span>
          </div>
          <ul
            className={`menu menu-items list-unstyled components`}
            onMouseLeave={this.mouseLeftTheMenu}
          >
            {Object.entries(menuItems).map(([levelOneKey, levelOneProps]) => (
              levelOneProps.label ? (
                <li
                  onMouseOver={this.openNavigation}
                  onMouseOut={this.closeNavigation}
                  onClick={() => {this.handleDirectClick(levelOneKey, levelOneProps)}}
                  className={`menu-item ${levelOneProps.active ? " active" : ""}`}
                >
                <span
                  className="menu-item-link dropdown-toggle"
                  id={`menu${levelOneKey}`}
                >
                  <span className={`iconmoon icon-${levelOneProps.menu_id.toLowerCase()}`}>
                  </span>
                </span>
                <ul
                  onMouseEnter={this.collapseLevelTwo}
                  className={`collapse collapsed-items list-unstyled ${active ? "active" : " "}`}

                >
                  <span className={"menu-item-name"}><Translate value={levelOneProps.label}/></span>
                  {Object.entries(levelOneProps.children).map(([levelTwoKey, levelTwoProps]) => {
                    const urlOptions = levelTwoKey.slice(1) +
                      (levelTwoProps.options !== null ? levelTwoProps.options : '')
                    if (levelTwoProps.label) {
                      return (
                        <li
                          className={
                            `second-level collapsed-item ${levelTwoProps.collapsed || (pageId == urlOptions) ? " active" : ""}`
                          }
                        >
                          {Object.keys(levelTwoProps.children).length > 0 ? (
                            <span
                              className="collapsed-level-item-link"
                              onMouseEnter={() => {this.collapseLevelThree(levelOneKey, levelTwoKey)}}
                            >
                              <Translate
                                value={levelTwoProps.hasOwnProperty('label') ? levelTwoProps.label : ''}
                              />
                            </span>
                          ) : (
                              <Link
                                onClick={() => {
                                  this.goToPage(
                                    routeMap.module + "?p=" + urlOptions,
                                    levelOneKey
                                  )
                                }}
                                className={`collapsed-level-item-link img-none ${(pageId == urlOptions) ? "active" : ""}`}
                                to={routeMap.module + "?p=" + urlOptions}
                              >
                                <Translate value={levelTwoProps.label}/>
                              </Link>
                            )}

                          <ul
                            onMouseOver={this.collapseLevelThree}
                            className={
                              `collapse-level collapsed-level-items first-level list-unstyled ${(levelOneProps.toggled && active) ? "active" : " "}`
                            }
                          >
                            {Object.entries(levelTwoProps.children).map(([levelThreeKey, levelThreeProps]) => {
                              return (
                                <React.Fragment>
                                  {Object.keys(levelTwoProps.children).length > 1 &&
                                    <span className="collapsed-level-title">
                                    </span>
                                  }
                                  {Object.entries(levelThreeProps).map(([levelFourKey, levelFourProps]) => {
                                    const urlOptions = levelFourKey.slice(1) +
                                      (levelFourProps.options !== null ? levelFourProps.options : '')
                                    if (levelFourProps.label) {
                                      return (
                                        <li
                                         className={`third-level collapsed-level-item `}
                                         onClick={this.activeCurrentElement}
                                        >
                                          <Link
                                            onClick={() => {
                                              // this.closeNavigation();
                                              this.goToPage(
                                                routeMap.module + "?p=" + urlOptions,
                                                levelOneKey
                                              );
                                            }}
                                            className={`collapsed-level-item-link`}
                                            to={routeMap.module + "?p=" + urlOptions}
                                          >
                                            <Translate value={levelFourProps.label}/>
                                          </Link>
                                        </li>
                                      );
                                    } else {
                                      return null
                                    }
                                  }
                                  )}
                                </React.Fragment>
                              )
                            })}
                          </ul>
                        </li>
                      );
                    } else {
                      return null
                    }
                  })}
                </ul>
              </li>) : null
            ))}
          </ul>
        </div>
      </nav>
    );
  }
}

const mapStateToProps = () => {}

const mapDispatchToProps = {
  setNavigation,
  updateTooltip
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(NavigationComponent));
