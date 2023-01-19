# Upsell Notices

To add an upsell notice, use the following code:

```
tribe( \Tribe\Admin\Upsell_Notice\Main::class )->render( [
	'text'    => 'Text to explain what you are promoting.',
	'link'    => [
		'text'   => 'Text for the link.',
		'url'    => 'https://url.com/to/more/info',
	],
] );
```

## Customizing the notice container

There are a couple of classes you can add that will display the upsell notice with different styles.

- `.tec-admin__upsell--rounded-corners` - Adds a rounded-corner, light gray background around the entire notice.
- `.tec-admin__upsell--rounded-corners-text` - Adds a rounded-corner, light gray background around the notice text, only.

Example:
```
tribe( \Tribe\Admin\Upsell_Notice\Main::class )->render( [
	'classes'  => [
		'tec-admin__upsell--rounded-corners'
	],
	'text'    => 'Text to explain what you are promoting.',
	'link'    => [
		'text'   => 'Text for the link.',
		'url'    => 'https://url.com/to/more/info',
	],
] );
```

## Customizing the notice link

Likewise, you can also add these classes to the link array to change the appearance.

- `.tec-admin__upsell-link--dark` - Changes the color to a dark color, instead of the default blue.
- `.tec-admin__upsell-link--underlined` - Adds an underline to the link text.

You can also change the following attributes of the link:

- `target` - Default is '_blank'.
- `rel` - Default is 'nofollow noreferrer'.

Example:
```
tribe( \Tribe\Admin\Upsell_Notice\Main::class )->render( [
	'classes'  => [
		'tec-admin__upsell--rounded-corners-text'
	],
	'text'    => 'Text to explain what you are promoting.',
	'link'    => [
		'classes' => [
			'tec-admin__upsell-link--dark',
			'tec-admin__upsell-link--underlined',
		],
		'text'    => 'Text for the link.',
		'url'     => 'https://url.com/to/more/info',
		'target'  => '_parent'
	],
] );
```