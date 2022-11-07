import { Button, Container } from '@mui/material';
import { styled } from '@mui/system';
import React, { useContext, useEffect, useState } from 'react';

import { ActionType, AppContext } from '../AppContext';
import { getLanguage, setLanguage } from '../localization';

const StyledContainer = styled(Container)({
  display: 'flex',
  justifyContent: 'flex-end',
});

export const UtilityBar = () => {
  const { dispatch } = useContext(AppContext);
  console.log(languageMapping);
  console.log(defaultLanguage);
  const current = getLanguage();
  console.log('current', current);
  // const [serverVersion, setServerVersion] = useState('');
  // const [serverLanguage, setServerLanguage] = useState('');

  // useEffect(() => {
  //   fetch('http://example.com/movies.json')
  // .then((response) => response.json())
  // .then((data) => console.log(data));

  // })
  return (
    <StyledContainer maxWidth='lg' disableGutters>
      utility bar
      <Button variant='contained' color='secondary' onClick={() => dispatch({ type: ActionType.SET_LANGUAGE, payload: 'en' })}>
        Change Language
      </Button>
    </StyledContainer>
  );
};
