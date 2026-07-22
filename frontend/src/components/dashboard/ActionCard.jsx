import Card from '../../design-system/components/Card';
import Button from '../../design-system/components/Button';

const ICONS = {
  person_add: 'person_add',
  emergency: 'emergency',
  schedule: 'schedule',
  default: 'notifications',
};

export default function ActionCard({ notification, onAction }) {
  const data = notification.action_data || {};
  const actions = data.actions || [];

  if (!notification.action_required || notification.action_completed) return null;

  return (
    <Card className="border-l-4 border-l-[#1A5632]">
      <div className="flex items-start gap-3">
        <span className="material-symbols-outlined text-2xl text-[#1A5632] mt-0.5">
          {ICONS[data.icon] || ICONS.default}
        </span>
        <div className="flex-1">
          <h3 className="font-semibold text-gray-900 text-sm">{data.title || notification.message}</h3>
          <p className="text-xs text-gray-500 mt-1">{data.description || ''}</p>
          <div className="flex gap-2 mt-3">
            {actions.map((action, i) => (
              <Button
                key={i}
                variant={action.variant === 'secondary' ? 'secondary' : 'primary'}
                size="sm"
                onClick={() => onAction(notification.id, action.action)}
              >
                {action.label}
              </Button>
            ))}
          </div>
        </div>
      </div>
    </Card>
  );
}
