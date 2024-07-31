<?php

namespace Automattic\Gravatar\PHPStanExtensions;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDoc\TypeNodeResolverAwareExtension;
use PHPStan\PhpDoc\TypeNodeResolverExtension;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectShapeType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\ShouldNotHappenException;

/**
 * Extension for PHPStan to support optional-properties-of custom type.
 */
class OptionalPropertiesOfTypeNodeResolverExtension implements TypeNodeResolverExtension, TypeNodeResolverAwareExtension {

	/**
	 * @var TypeNodeResolver
	 */
	private $type_node_resolver;

	/**
	 * @param TypeNodeResolver $type_node_resolver
	 *
	 * @return void
	 */
	public function setTypeNodeResolver( $type_node_resolver ): void {
		$this->type_node_resolver = $type_node_resolver;
	}

	/**
	 * @param TypeNode $type_node
	 * @param NameScope $name_scope
	 *
	 * @return Type|null
	 * @throws ShouldNotHappenException
	 */
	public function resolve( $type_node, $name_scope ): ?Type {
		if ( ! $type_node instanceof GenericTypeNode ) {
			return null;
		}

		$type_name = $type_node->type;
		if ( $type_name->name !== 'optional-properties-of' ) {
			return null;
		}

		$argument_types = array_map( fn( $argument ) => $this->type_node_resolver->resolve( $argument, $name_scope ), $type_node->genericTypes );
		$scope = new OutOfClassScope();
		$merged_type_builder = ConstantArrayTypeBuilder::createEmpty();
		foreach ( $argument_types as $argument_type ) {
			if ( $argument_type instanceof ObjectType ) {
				// Use PHP Reflection to get properties
				$reflection_class = $argument_type->getClassReflection();
				if ( is_null( $reflection_class ) ) {
					throw new ShouldNotHappenException( 'could not get reflection class for argument type' );
				}

				$php_reflection_class = new \ReflectionClass( $reflection_class->getName() );
				foreach ( $php_reflection_class->getProperties() as $property ) {
					if ( $reflection_class->hasProperty( $property->name ) ) {
						$reflection_property = $reflection_class->getProperty( $property->name, $scope );
						if ( ! $reflection_property->isPublic() ) {
							continue;
						}
						$readable_type = $reflection_property->getReadableType();
						$merged_type_builder->setOffsetValueType( new ConstantStringType( $property->name ), $readable_type, true, );
					}
				}
			} else if ( $argument_type instanceof ObjectShapeType ) {
				foreach ( $argument_type->getProperties() as $property_name => $property_type ) {
					$merged_type_builder->setOffsetValueType( new ConstantStringType( $property_name ), $property_type, true, );
				}
			} else {
				throw new ShouldNotHappenException( 'optional-properties-of only supports object types or phpstan type that resolve to objects ' );
			}
		}

		return $merged_type_builder->getArray();
	}
}
