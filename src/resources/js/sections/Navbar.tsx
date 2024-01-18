import { Button, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';
import { useContext } from 'react';
import { Link, useNavigate } from 'react-router-dom';

import { AppContext } from '../AppContext';
import RootServerApi from '../RootServerApi';
import { strings } from '../localization';

const StyledNavBarWrapper = styled('div')(({ theme }) => ({
  paddingTop: theme.spacing(1),
  paddingBottom: theme.spacing(1),
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  marginBottom: theme.spacing(4),
}));

const StyledUserInfo = styled('div')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  [theme.breakpoints.up('md')]: {
    flexDirection: 'row',
  },
}));

const StyledNavWrapper = styled('nav')(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  [theme.breakpoints.up('md')]: {
    flexDirection: 'row',
  },
  '& > a': {
    marginRight: theme.spacing(),
  },
}));

export const Navbar = () => {
  const { state } = useContext(AppContext);
  const navigate = useNavigate();
  const handleLogout = async () => {
    await RootServerApi.logout();
    RootServerApi.token = null;
    navigate('/login');
  };

  return (
    <Container maxWidth='lg'>
      <StyledNavBarWrapper>
        <StyledNavWrapper>
          <Link to='/meetings'>{strings.meetingsTitle}</Link>
          <Link to='/service-bodies'>{strings.serviceBodiesTitle}</Link>
          <Link to='/users'>{strings.usersTitle}</Link>
          <Link to='/formats'>{strings.formatsTitle}</Link>
          <Link to='/my-account'>{strings.myAccountTitle}</Link>
        </StyledNavWrapper>
        <StyledUserInfo>
          <Typography color='dark.main'>{state.user?.displayName ?? ''}</Typography>
          <Button variant='contained' color='primary' onClick={handleLogout}>
            {strings.signOutTitle}
          </Button>
        </StyledUserInfo>
      </StyledNavBarWrapper>
    </Container>
  );
};
