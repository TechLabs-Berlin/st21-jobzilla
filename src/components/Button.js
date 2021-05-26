import React from 'react';
import './Button.css';
import { Link } from 'react-router-dom';

const Design = ['button--primary', 'button--transparent'];

const Sizes = ['button--medium', 'button--large'];

export const Button = (
    {
        children,
        type,
        onClick,
        buttonDesign,
        buttonSize
    }) => {
        const checkButtonDesign = Design.includes(buttonDesign)
            ? buttonDesign
            : Design[0];

        const checkButtonSize = Sizes.includes(buttonSize) ? buttonSize : Sizes[0]

        return (
            <Link to='/myAccount' className='button-mobile'>
                <button className={`button ${checkButtonDesign} ${checkButtonSize}`}
                onClick={onClick}
                type={type}
                >
                    {children}
                </button>
            </Link>
        )

    };


    