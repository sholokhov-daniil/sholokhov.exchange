export const view = (target, data) => {
    const registry = Sholokhov.Exchange.Detail.entityRegistry;

    if (!registry.has(target.type)) {
        return;
    }

    const render = registry.get(target.type);

    render(data);
}

export const registration = (template, callback) => {
    const registry = Sholokhov.Exchange.Detail.entityRegistry;
    registry.set(template, callback);
}