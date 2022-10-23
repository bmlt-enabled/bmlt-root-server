import { Box, Typography } from '@mui/material';
import { styled } from '@mui/system';

const StyledErrorMessage = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
}));

interface IProps {
  message: string | null;
}

export const FormSubmitError = (props: IProps) => {
  return (
    <StyledErrorMessage>
      <Typography variant='caption' color='error.main'>
        {props.message}
      </Typography>
    </StyledErrorMessage>
  );
};
