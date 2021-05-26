import React from 'react';
import '../App.css';
import { Button } from './Button';
import './MainSection.css';


function MainSection() {
    return (
      
          <li className='main-container'>
        
     
     <h1>Jobzilla is the best cover letter generator on-line</h1>
     <p>Create your cover letter</p>
     
     <div className='main-buttons'>
       
       <Button
         className='buttons'
         buttonDesign='button--primary'
         buttonSize='button--large'
        
       >
           Log in 
       
       </Button>
       <Button
         className='buttons'
         buttonDesign='button--transparent'
         buttonSize='button--large'
       >
         Sign up
       </Button>
       <Button
         className='buttons'
         buttonDesign='button--transparent'
         buttonSize='button--large'
       >
         Continue as a guest
       </Button>
     </div>
 </li>
 );
}
           
      

    

    

export default MainSection;