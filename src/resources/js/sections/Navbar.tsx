import { Button, Container, Typography } from '@mui/material';
import { styled } from '@mui/system';
import { useContext } from 'react';
import { Link, useNavigate } from 'react-router-dom';

import { AppContext } from '../AppContext';
import RootServerApi from '../RootServerApi';

const StyledNavBarWrapper = styled('div')(({ theme }) => ({
  paddingTop: theme.spacing(1),
  paddingBottom: theme.spacing(1),
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
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
          <Link to='/meetings'>Meetings</Link>
          <Link to='/service-bodies'>Service Bodies</Link>
          <Link to='/users'>Users</Link>
          <Link to='/meeting-formats'>Formats</Link>
          <Link to='/my-account'>My Account</Link>
        </StyledNavWrapper>
        <StyledUserInfo>
          <Typography color='dark.main'>{state.user?.displayName ?? ''}</Typography>
          <Button variant='contained' color='primary' onClick={handleLogout}>
            Logout
          </Button>
        </StyledUserInfo>
      </StyledNavBarWrapper>
    </Container>
  );
};
