import { FormControl } from '@mui/material';
import { styled } from '@mui/system';

const FormControlWrapper = styled(FormControl)(({ theme }) => ({
  width: '100%',
  marginBottom: theme.spacing(4),
}));

export default FormControlWrapper;
