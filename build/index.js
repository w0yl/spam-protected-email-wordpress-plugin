(function (wp) {
    const { registerFormatType, applyFormat, removeFormat, getActiveFormat } = wp.richText;
    const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
    const { createElement, useState, useEffect } = wp.element;
    const { Popover, TextControl, Button } = wp.components;

    const FORMAT_NAME = 'spam-protected-email/inline-email';

    const EmailPopoverUI = ({ value, onChange, onClose, isActive }) => {
        const activeFormat = getActiveFormat(value, FORMAT_NAME);
        const existingEmail = activeFormat?.attributes?.['data-spe-email'];
        const selectedText = value.text.slice(value.start, value.end).trim();
        const initialEmail = existingEmail || (selectedText.includes('@') ? selectedText : '');
        const [email, setEmail] = useState(initialEmail);

        useEffect(() => {
            if (existingEmail) {
                setEmail(existingEmail);
            }
        }, [existingEmail]);

        const handleSubmit = (e) => {
            e.preventDefault();
            if (email) {
                onChange(
                    applyFormat(value, {
                        type: FORMAT_NAME,
                        attributes: {
                            class: 'spe-email-link',
                            href: '#',
                            'data-spe-email': email
                        }
                    })
                );
            }
            onClose();
        };

        const handleRemove = () => {
            onChange(removeFormat(value, FORMAT_NAME));
            onClose();
        };

        return createElement(
            Popover,
            { onClose: onClose, position: 'bottom center' },
            createElement(
                'form',
                { onSubmit: handleSubmit, style: { padding: '12px', width: '260px' } },
                createElement(TextControl, {
                    label: 'Target Email Address',
                    value: email,
                    placeholder: 'name@example.com',
                    onChange: (val) => setEmail(val)
                }),
                createElement(
                    'div',
                    { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '8px' } },
                    isActive && createElement(
                        Button,
                        { isDestructive: true, isSmall: true, onClick: handleRemove },
                        'Remove'
                    ),
                    createElement(
                        Button,
                        { isPrimary: true, isSmall: true, type: 'submit' },
                        'Apply'
                    )
                )
            )
        );
    };

    registerFormatType(FORMAT_NAME, {
        title: 'Spam Protected Email',
        tagName: 'a',
        className: 'spe-email-link',
        attributes: {
            class: 'class',
            href: 'href',
            'data-spe-email': 'data-spe-email'
        },
        edit: function ({ value, onChange, isActive }) {
            const [isPopoverOpen, setIsPopoverOpen] = useState(false);

            return createElement(
                wp.element.Fragment,
                null,
                createElement(RichTextToolbarButton, {
                    icon: 'email',
                    title: 'Protect Email',
                    onClick: () => setIsPopoverOpen(!isPopoverOpen),
                    isActive: isActive,
                }),
                isPopoverOpen && createElement(EmailPopoverUI, {
                    value: value,
                    onChange: onChange,
                    onClose: () => setIsPopoverOpen(false),
                    isActive: isActive
                })
            );
        },
    });
})(window.wp);