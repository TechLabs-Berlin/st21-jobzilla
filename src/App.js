import React from 'react';
import NavigationBar from './components/NavigationBar';
import './App.css';
import Home from './components/pages/Home.js';
import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import SignUp from './components/pages/SignUp';
import AboutUs from './components/pages/AboutUs';
import ContinueAsAGuest from './components/pages/ContinueAsAGuest';

function App() {
  return (
    <>
  
      <Router>
        <NavigationBar />
        <Switch>
          <Route path='/' exact component={Home} />
          <Route path='/SignUp' component={SignUp} />
          <Route path='/AboutUs' component={AboutUs} />
          <Route path='/ContinueAsAGuest' component={ContinueAsAGuest} />
        </Switch>
      </Router>
    </>
  );
}

export default App;