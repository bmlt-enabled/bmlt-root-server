import CircularProgress from '@mui/material/CircularProgress';
import { Box } from '@mui/material';
import { styled } from '@mui/system';

const StyledSpinnerContainer = styled(Box)({
  position: 'absolute',
  top: '50%',
  left: '50%',
  transform: 'translate(-50%, -50%)',
  zIndex: 1000,
});

export const LoadingSpinner = () => {
  return (
    <StyledSpinnerContainer>
      <CircularProgress size={32} />
    </StyledSpinnerContainer>
  );
};
