
/**
 * @see https://prettier.io/docs/configuration
 * @type {import("prettier").Config}
 */
const config = {
  jsxSingleQuote: true,
  singleQuote: true,
  semi: true,
  tabWidth: 2,
  bracketSameLine: false,
  useTabs: false,
  arrowParens: "always",
  endOfLine: "auto",
  trailingComma: "none",
  printWidth: 200,
  plugins: ["prettier-plugin-svelte", "prettier-plugin-tailwindcss"],
  overrides: [
    {
      files: "*.svelte",
      options: {
        parser: "svelte"
      }
    }
  ]
};

export default config;
