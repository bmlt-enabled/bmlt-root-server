import GitHubIcon from '@mui/icons-material/GitHub';
import { IconButton } from '@mui/material';
import { styled } from '@mui/system';

const StyledButton = styled(IconButton)(({ theme }) => ({
  color: theme.palette.common.white,
  marginRight: theme.spacing(0.5),
  marginLeft: theme.spacing(0.5),
}));

export const GithubButton = () => {
  const handleLink = () => {
    window.open('https://github.com/bmlt-enabled/bmlt-root-server', '_blank');
  };
  return (
    <StyledButton onClick={handleLink}>
      <GitHubIcon />
    </StyledButton>
  );
};
