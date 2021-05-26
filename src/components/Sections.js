import React from 'react';
import './Sections.css';
import Part from './Part.js';
import MainSection from './MainSection.js';

function Sections() {
  return (
    <div className='sections'>
  
     
          <ul className='sections__items'>
            <MainSection/>

            <img src="./images/Steps.png" className="steps" alt="cannot display"/>
          </ul>
        
        </div>
 
  );
}

export default Sections;