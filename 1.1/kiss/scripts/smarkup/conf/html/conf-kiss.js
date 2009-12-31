SMarkUp.conf.html = {
	onCtrlEnter: {open: "\n<br />"},
	preview: {
		template: '~/templates/preview.html',
		autoRefresh: false
	},
	markup: [
		SMarkUp.addons.searchAndReplace,
		SMarkUp.addons.preview,
		{separator: true},
		{
			name: 'blockquote',
			title: 'Blockquote',
			open: '<blockquote>',
			close: '</blockquote>',
		},
        {
            name: 'code',
            title: 'Code',
            open: '<code>',
            close: '</code>',
        },
		{
			separator: true
		},
		{
			name: 'strong',
			title: 'Bold',
			open: '<strong>',
			close: '</strong>',
			key: 'B'
		},
		{
			name: 'em',
			title: 'Italic',
			key: 'I',
			open: '<em>',
			close: '</em>'
		},
		{
			name: 'del',
			title: 'Strike Through',
			open: '<del>',
			close: '</del>'
		},
		{
			separator: true
		},
		{
			name: 'ul',
			title: 'Unordered List',
			open: '<ul>',
			close: '</ul>',
			prepend: "\n",
			wrapSelection: "\n   <li>{selection}</li>\n",
			wrapMultiline: true
		},
		{
			name: 'ol',
			title: 'Ordered List',
			open: '<ol>',
			close: '</ol>',
			prepend: "\n",
			wrapSelection: "\n   <li>{selection}</li>\n",
			wrapMultiline: true
		},
		{
			name: 'li',
			title: 'List Item',
			open: '<li>',
			close: '</li>',
			prepend: "\n   ",
			wrapMultiline: true
		},
		{
			separator: true
		},
		{
			open: '<img{attributes}/>',
			name: 'img',
			title: 'Image',
			attributes: [
				{
					type: 'text',
					name: 'src',
					label: 'Image URL'
				},
				{
					type: 'text',
					name: 'alt',
					label: 'Alt'
				}
			]
		},
		{
			open: '<a{attributes}>',
			close: '</a>',
			name: 'a',
			title: 'Link',
			attributes: [
				{
					name: 'href',
					type: 'text',
					label: 'Link URL'
				},
				{
					name: 'title',
					type: 'text',
					label: 'Title'
				},
				{
					name: 'rel',
					type: 'text',
					label: 'Rel'
				}
			]
		},
		{separator: true},
		SMarkUp.addons.help
	]
};
